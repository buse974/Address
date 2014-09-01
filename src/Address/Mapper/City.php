<?php

namespace Address\Mapper;

use Zend\Db\Sql\Predicate\Predicate;
class City extends AbstractMapper
{
    /**
     * Get list cities
     * @param  array                       $filter
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function getList($filter=null)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id' , 'name', 'libelle','code', 'longitude', 'latitude'));
        $select->join('division','division.id=city.division_id',array('id' ,'name', 'short_name','code'), $select::JOIN_LEFT);
        $select->join('country','country.id=city.country_id',array('id' , 'short_name','name'));

        if (!empty($filter)) {
            if (!empty($filter['search'])) {
                $search = preg_replace('/\s\s+/', ' ', $filter['search']);
                $select->where(array('(city.libelle LIKE ? ' =>  $search . '%'));
                $select->where(array('division.name LIKE ? ' =>  $search . '%'),Predicate::OP_OR);
                $select->where(array('city.name LIKE ? )' => $search . '%'),Predicate::OP_OR);
            }
            if (!empty($filter['country']) && is_numeric($filter['country'])) {
                $select->where(array('country.id' => $filter['country']));
            }
            if (!empty($filter['division']) && is_numeric($filter['division'])) {
                $select->where(array('division.id' => $filter['division']));
            }
            if (!empty($filter['sort']) && !empty($filter['sort']['field']) && !empty($filter['sort']['direction'])) {
                $select->order(array($filter['sort']['field'] => $filter['sort']['direction']));
            }
        }

       return  $this->selectWith($select);
    }

    public function getCityId($city ,$division ,$country)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id' , 'name', 'libelle','code', 'longitude', 'latitude'));
        $select->where(array('city.name' =>  $city));

        if ($division && is_numeric($division)) {
            $select->where(array('city.division_id' =>  $division));
        }
        if ($country && is_numeric($country)) {
            $select->where(array('city.country_id' =>  $country));
        }

        return  $this->selectWith($select);
    }
}
