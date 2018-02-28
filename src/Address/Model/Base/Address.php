<?php

namespace Address\Model\Base;

use Dal\Model\AbstractModel;

class Address extends AbstractModel
{
    protected $id;
    protected $street_no;
    protected $street_type;
    protected $street_name;
    protected $building;
    protected $apartment;
    protected $door;
    protected $floor;
    protected $city_id;
    protected $division_id;
    protected $country_id;
    protected $longitude;
    protected $latitude;
    protected $timezone;
    protected $full_address;

    protected $prefix = 'address';

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getStreetNo()
    {
        return $this->street_no;
    }

    public function setStreetNo($street_no)
    {
        $this->street_no = $street_no;

        return $this;
    }

    public function getStreetType()
    {
        return $this->street_type;
    }

    public function setStreetType($street_type)
    {
        $this->street_type = $street_type;

        return $this;
    }

    public function getStreetName()
    {
        return $this->street_name;
    }

    public function setStreetName($street_name)
    {
        $this->street_name = $street_name;

        return $this;
    }

    public function getCityId()
    {
        return $this->city_id;
    }

    public function setCityId($city_id)
    {
        $this->city_id = $city_id;

        return $this;
    }

    public function getDivisionId()
    {
        return $this->division_id;
    }

    public function setDivisionId($division_id)
    {
        $this->division_id = $division_id;

        return $this;
    }

    public function getCountryId()
    {
        return $this->country_id;
    }

    public function setCountryId($country_id)
    {
        $this->country_id = $country_id;

        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getDoor()
    {
        return $this->door;
    }

    public function setDoor($door)
    {
        $this->door = $door;

        return $this;
    }

    public function getBuilding()
    {
        return $this->building;
    }

    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    public function setApartment($apartment)
    {
        $this->apartment = $apartment;

        return $this;
    }

    public function getApartment()
    {
        return $this->apartment;
    }

    public function getFloor()
    {
        return $this->floor;
    }

    public function setFloor($floor)
    {
        $this->floor = $floor;

        return $this;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getFullAddress()
    {
        return $this->full_address;
    }

    public function setFullAddress($full_address)
    {
        $this->full_address = $full_address;

        return $this;
    }
}
