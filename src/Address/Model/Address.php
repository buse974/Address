<?php

namespace Address\Model;

use Address\Model\Base\Address as BaseAddress;

class Address extends BaseAddress
{
    protected $city;
    protected $division;
    protected $country;

    public function exchangeArray(array &$data)
    {
        parent::exchangeArray($data);

        $this->city = $this->requireModel('addr_model_city', $data);
        $this->division = $this->requireModel('addr_model_division', $data);
        $this->country = $this->requireModel('addr_model_country', $data);
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
