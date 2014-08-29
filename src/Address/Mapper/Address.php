<?php

namespace Address\Mapper;

class Address extends AbstractMapper
{
    /**
     *
     * @param  number                 $address
     * @return \Address\Model\Address
     */
    public function get($address)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id','street_no','street_type','street_name','longitude','latitude','timezone'))
               ->join('city','city.id=address.city_id',array('id' , 'name', 'libelle','code', 'longitude', 'latitude'),$select::JOIN_LEFT)
               ->join('division','division.id=address.division_id',array('id' ,'name', 'short_name','code'), $select::JOIN_LEFT)
               ->join('country','country.id=address.country_id',array('id' , 'short_name','name'), $select::JOIN_LEFT)
               ->where(array('address.id' => $address));

        return  $this->selectWith($select);
    }
    
    /**
     *
     * @param  number                 $address
     * @return \Address\Model\Address
     */
    public function getList()
    {
    	$select = $this->tableGateway->getSql()->select();
    	$select->columns(array('id','street_no','street_type','street_name','longitude','latitude','timezone'))
    	->join('city','city.id=address.city_id',array('id' , 'name', 'libelle','code', 'longitude', 'latitude'),$select::JOIN_LEFT)
    	->join('division','division.id=address.division_id',array('id' ,'name', 'short_name','code'), $select::JOIN_LEFT)
    	->join('country','country.id=address.country_id',array('id' , 'short_name','name'), $select::JOIN_LEFT);
    
    	return  $this->selectWith($select);
    }
}
