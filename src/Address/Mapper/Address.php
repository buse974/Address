<?php
namespace Address\Mapper;

use Dal\Mapper\AbstractMapper;

class Address extends AbstractMapper
{

    /**
     *
     * @param integer $address            
     * @return \Dal\Dal\Db\ResultSet
     */
    public function get($address)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id','street_no','street_type','street_name','floor','door','apartment','building','longitude','latitude','timezone'))
            ->join('city', 'city.id=address.city_id', array('id','name','libelle','code','longitude','latitude'), $select::JOIN_LEFT)
            ->join('division', 'division.id=address.division_id', array('id','name','short_name','code'), $select::JOIN_LEFT)
            ->join('country', 'country.id=address.country_id', array('id','short_name','name'), $select::JOIN_LEFT)
            ->where(array('address.id' => $address));
        
        return $this->selectWith($select);
    }

    /**
     *
     * @param integer $address            
     * @return \Dal\Dal\Db\ResultSet
     */
    public function selectByArray($address, $city = null, $division = null, $country = null)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id','street_no','street_type','street_name','floor','door','apartment','building','longitude','latitude','timezone'))
            ->join('city', 'city.id=address.city_id', array('id','name','libelle','code','longitude','latitude'), $select::JOIN_LEFT)
            ->join('division', 'division.id=address.division_id', array('id','name','short_name','code'), $select::JOIN_LEFT)
            ->join('country', 'country.id=address.country_id', array('id','short_name','name'), $select::JOIN_LEFT);
        
        if (! empty($address['street_no'])) {
            $select->where(array('address.street_no' => $address['street_no']));
        } else {
            $select->where(array('address.street_no IS NULL'));
        }
        if (! empty($address['street_type'])) {
            $select->where(array('address.street_type' => $address['street_type']));
        } else {
            $select->where(array('address.street_type IS NULL'));
        }
        if (! empty($address['street_name'])) {
            $select->where(array('address.street_name' => $address['street_name']));
        } else {
            $select->where(array('address.street_name IS NULL'));
        }
        if (! empty($address['floor'])) {
            $select->where(array('address.floor' => $address['floor']));
        } else {
            $select->where(array('address.floor IS NULL'));
        }
        if (! empty($address['door'])) {
            $select->where(array('address.door' => $address['door']));
        } else {
            $select->where(array('address.door IS NULL'));
        }
        if (! empty($address['apartment'])) {
            $select->where(array('address.apartment' => $address['apartment']));
        } else {
            $select->where(array('address.apartment IS NULL'));
        }
        if (! empty($address['building'])) {
            $select->where(array('address.building' => $address['building']));
        } else {
            $select->where(array('address.building IS NULL'));
        }
        if (is_numeric($city) && $city > 0) {
            $select->where(array('address.city_id' => $city));
        } else {
            $select->where(array('address.city_id IS NULL'));
        }
        if (is_numeric($division) && $division > 0) {
            $select->where(array('address.division_id' => $division));
        } else {
            $select->where(array('address.division_id IS NULL'));
        }
        if (is_numeric($country) && $country > 0) {
            $select->where(array('address.country_id' => $country));
        } else {
            $select->where(array('address.country_id IS NULL'));
        }
        
        return $this->selectWith($select);
    }

    /**
     *
     * @param integer $address            
     * @return \Dal\Dal\Db\ResultSet
     */
    public function getList()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->columns(array('id','street_no','street_type','street_name','floor','door','apartment','building','longitude','latitude','timezone'))
            ->join('city', 'city.id=address.city_id', array('id','name','libelle','code','longitude','latitude'), $select::JOIN_LEFT)
            ->join('division', 'division.id=address.division_id', array('id','name','short_name','code'), $select::JOIN_LEFT)
            ->join('country', 'country.id=address.country_id', array('id','short_name','name'), $select::JOIN_LEFT);
        
        return $this->selectWith($select);
    }
}
