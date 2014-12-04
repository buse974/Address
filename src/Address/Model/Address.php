<?php

namespace Address\Model;

use Address\Model\Base\Address as BaseAddress;
use Address\Model\City;
use Address\Model\Division;
use Address\Model\Country;

class Address extends BaseAddress
{
    protected $city;
    protected $division;
    protected $country;

    public function exchangeArray(array &$data)
    {
        parent::exchangeArray($data);

        $this->city = new City($this);
        $this->division = new Division($this);
        $this->country = new Country($this);

        $this->city->exchangeArray($data);
        $this->division->exchangeArray($data);
        $this->country->exchangeArray($data);
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    public function getDivision()
    {
        return $this->division;
    }

    public function setDivision($division)
    {
        $this->division = $division;

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }
}
