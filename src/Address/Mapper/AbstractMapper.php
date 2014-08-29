<?php

namespace Address\Mapper;

use Address\Db\Sql\Select;
use Address\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Address\Model\AbstractModel;

abstract class AbstractMapper implements ServiceLocatorAwareInterface
{
    /**
     *
     * @var \Address\Db\TableGateway\TableGateway
     */
    protected $tableGateway;
    protected $paginator;
    protected $paginatorOptions = array();
    protected $usePaginator;
    protected $serviceLocator;

    /**
     *
     * @var \Address\Db\ResultSet\ResultSet
     */
    protected $result;

    /**
     *
     * @var array
     */
    protected $primary_key;

    /**
     * Construct the model with the tablegateway
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Update a modele
     * @param  \Address\Model\AbstractModel    $model
     * @return \Address\Db\ResultSet\ResultSet
     */
    public function select(AbstractModel $model,$order =null)
    {
        if ($this->usePaginator) {
            $this->usePaginator = false;

            $sl = $this->tableGateway->getSql()->select();

            $sl->where($model->toArrayCurrent());
            if ($order) {
                $sl->order($order);
            }
            $paginator = $this->initPaginator($sl);

            return $paginator;
        }

        $this->result = $this->tableGateway->select($model->toArrayCurrent());

        return $this->result;
    }

    public function requestPdo($request,$param = null)
    {
        return $this->tableGateway->requestPdo($request,$param);
    }

    /**
     *
     * @return \Address\Db\ResultSet\ResultSet
     */
    public function selectPdo($select,$param = null)
    {
        return $this->tableGateway->selectPdo($select,$param);
    }

    /**
     * Get request
     * @param \Zend\Db\ResultSet\ResultSet
     */
    public function selectWith(\Zend\Db\Sql\Select $select)
    {
        if ($this->usePaginator) {
            $this->usePaginator = false;

            return $this->initPaginator($select);
        }

        $this->result = $this->tableGateway->selectWith($select);

        return $this->result;
    }

    /**
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll()
    {
        if ($this->usePaginator) {
            $this->usePaginator = false;

            return $this->initPaginator($this->tableGateway->getSql()->select());
        }
        $this->result =  $this->tableGateway->select();

        return $this->result;
    }

    /**
     * delete request
     * @param \Zend\Db\ResultSet\ResultSet
     */
    public function deleteWith(\Zend\Db\Sql\Delete $delete)
    {
        return $this->tableGateway->deleteWith($delete);
    }

    /**
     * delete request
     * @param \Zend\Db\ResultSet\ResultSet
     */
    public function insertWith(\Zend\Db\Sql\Insert $insert)
    {
        return $this->tableGateway->insertWith($insert);
    }

    /**
     * update request
     * @param \Zend\Db\ResultSet\ResultSet
     */
    public function updateWith(\Zend\Db\Sql\Update $update)
    {
        return $this->tableGateway->updateWith($update);
    }

    protected function fetchRow($column, $value)
    {
        if (is_int($value)) {
            $where = $column . ' = ' . $value;
        } else {
            $where = array($column, $value);
        }
        $resultSet = $this->tableGateway->select($where);
        $result = $resultSet->current();

        return $result;
    }

    /**
     * Insert a new modele
     * @param  \Address\Model\AbstractModel $model
     * @return integer
     */
    public function insert(AbstractModel $model)
    {
        return $this->tableGateway->insert($model->toArrayCurrent());
    }

    public function getLastInsertValue()
    {
        return $this->tableGateway->getLastInsertValue();
    }

    /**
     * Update a modele
     * @param  \Address\Model\AbstractModel $model
     * @return integer
     */
    public function update(AbstractModel $model,$where = null)
    {
        $datas = $model->toArrayCurrent();

        if ($where === null) {
             foreach ($this->tableGateway->getPrimaryKey() as $key) {
                $where[$key] = $datas[$key];
                unset($datas[$key]);
            }
        }

        return (count($datas) > 0) ?
            $this->tableGateway->update($datas,$where) : false;
    }

    /**
     * Delete full modele
     *
     * @param  \Address\Model\AbstractModel $model
     * @return boolean
     */
    public function delete(AbstractModel $model)
    {
        return $this->tableGateway->delete($model->toArray());
    }

    /**
     * Set the mapper options and enable the mapper
     *
     * @param  array          $options
     * @return AbstractMapper
     */
    public function usePaginator(array $options = array())
    {
        $this->usePaginator = true;
        $this->paginatorOptions = array_merge($this->paginatorOptions, $options);

        return $this;
    }

    /**
     * Check If option n and p exist. if exist usePagination is true else false
     *
     * @param  array                          $options
     * @return \Address\Mapper\AbstractMapper
     */
    public function checkUsePagination($options=array())
    {
        if ($options!==null && is_array($options)) {
            if (array_key_exists('n', $options) && array_key_exists('p', $options)) {
                $this->usePaginator(array('n' => $options['n'],'p' => $options['p']));
            }
        }

        return $this;
    }

    /**
     * Init the paginator with a select object
     * @param  Zend\Db\Sql\Select $select
     * @return Paginator
     */
    public function initPaginator($select)
    {
        $this->paginator = new Paginator(new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $this->tableGateway->getResultSetPrototype()
        ));

        $options = $this->getPaginatorOptions();
        if (!isset($options['n'])) {
            $options['n'] = 10;
        }
        $this->paginator->setItemCountPerPage($options['n']);

        if (!isset($options['p'])) {
            $options['p'] = 1;
        }

        return ($this->paginator->count() < $options['p']) ? (new \Address\Db\ResultSet\ResultSet)->initialize(array()) : $this->paginator->getItemsByPage($options['p']);
    }

    /**
     *
     * @return integer
     */
    public function count()
    {
        $pag = $this->getPaginator();

        if ($pag instanceof \Zend\Paginator\Paginator) {
            return $pag->getTotalItemCount();
        }

        return $this->result->count();
    }

    public function printSql($select)
    {
        return $select->getSqlString($this->tableGateway->getAdapter()->getPlatform());
    }

    public function getPaginatorOptions()
    {
        return $this->paginatorOptions;
    }

    /**
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     *
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
    */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
