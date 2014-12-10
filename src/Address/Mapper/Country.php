<?php

namespace Address\Mapper;

use Zend\Db\Sql\Predicate\Predicate;
use Dal\Mapper\AbstractMapper;

class Country extends AbstractMapper
{
    public function getList($filter)
    {
        $select = $this->tableGateway->getSql()->select();

        $select->columns(array('id','short_name', 'name'));

        if (isset($filter['search'])) {
            $search = preg_replace('/\s\s+/', ' ', $filter['search']);
            $select->where(array('short_name LIKE ?' => $search . '%'));
        }

        return $this->selectWith($select);
    }

    public function getCountryByName($country)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id','name','short_name'));
        $select->where(array('(country.name = ? ' =>  $country, 'country.short_name = ? )' => $country),Predicate::OP_OR);

        return $this->selectWith($select);
    }
}
