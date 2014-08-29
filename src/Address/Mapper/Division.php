<?php

namespace Address\Mapper;

use Zend\Db\Sql\Predicate\Predicate;

class Division extends AbstractMapper
{
    public function getList($filter)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id' , 'name', 'short_name','code'));
        $select->join('country','country.id=division.country_id',array('id' ,'name', 'short_name'));

        if (!empty($filter)) {
            if (!empty($filter['search'])) {
                $search = preg_replace('/\s\s+/', ' ', $filter['search']);
                $select->where(array('(division.name LIKE ? ' =>  $search . '%'));
                $select->where(array('country.name LIKE ? )' => $search . '%'),Predicate::OP_OR);
            }

            if (!empty($filter['country']) && is_numeric($filter['country'])) {
                $select->where(array('country.id' =>  $filter['country']));
            }
        }

        return  $this->selectWith($select);
    }

    public function getStateId($state, $country=null)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id'));
        $select->where(array('(division.name = ? ' =>  $state));
        $select->where(array('division.short_name = ? )' => $state),Predicate::OP_OR);

        if ($country && is_numeric($country)) {
            $select->where(array('division.country_id' =>  $country));
        }

        return  $this->selectWith($select);
    }
}
