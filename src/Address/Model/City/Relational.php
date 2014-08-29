<?php

namespace Address\Model\City;

use Address\Model\Country;
use Address\Model\Division;
use Address\Model\City;

class Relational extends City
{
    protected $division;
    protected $country;

    public function exchangeArray(array &$data)
    {
        parent::exchangeArray($data);

        $this->country = new Country($this);
        $this->division = new Division($this);

        $this->country->exchangeArray($data);
        $this->division->exchangeArray($data);
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

    public function getDivision()
    {
        return $this->division;
    }

    public function setDivision($division)
    {
        $this->division = $division;

        return $this;
    }
}
