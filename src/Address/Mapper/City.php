<?php

namespace Address\Mapper;

use Zend\Db\Sql\Predicate\Predicate;
use Dal\Mapper\AbstractMapper;

class City extends AbstractMapper
{
    /**
     * Get list cities
     * @param  array                       $filter
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function getList(array $filter= array())
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id' , 'name', 'libelle', 'code', 'longitude', 'latitude'));
        $select->join('division','division.id=city.division_id',array('id' ,'name', 'short_name', 'code'), $select::JOIN_LEFT);
        $select->join('country','country.id=city.country_id',array('id' , 'short_name', 'name'));

        if (isset($filter['search'])) {
                $search = preg_replace('/\s\s+/', ' ', $filter['search']);
                $select->where(array('(city.libelle LIKE ? ' =>  $search . '%'));
                $select->where(array('division.name LIKE ? ' =>  $search . '%'),Predicate::OP_OR);
                $select->where(array('city.name LIKE ? )' => $search . '%'),Predicate::OP_OR);
        } 
        if (!empty($filter['country']) && is_numeric($filter['country']) && $filter['country'] > 0) {
        	$select->where(array('country.id' => $filter['country']));
        }
        if (!empty($filter['division']) && is_numeric($filter['division']) && $filter['division'] > 0) {
            $select->where(array('division.id' => $filter['division']));
        }
        if (isset($filter['sort']) && isset($filter['sort']['field']) && isset($filter['sort']['direction'])) {
        	$select->order(array($filter['sort']['field'] => $filter['sort']['direction']));
        }
        
        return  $this->selectWith($select);
    }

    public function getCityByName($city , $division = null, $country = null)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id' , 'name', 'libelle', 'code', 'longitude', 'latitude'));
        $select->where(array('city.name' =>  $city));

        if (is_numeric($division) && $division > 0) {
            $select->where(array('city.division_id' =>  $division));
        }
        if (is_numeric($country) && $country > 0) {
            $select->where(array('city.country_id' =>  $country));
        }

        return  $this->selectWith($select);
    }
}
