<?php

namespace Address\Model;

use Address\Model\Country;
use Address\Model\Base\Division as BaseDivision;

class Division extends BaseDivision
{
    protected $country;

    public function exchangeArray(array &$data)
    {
        parent::exchangeArray($data);

        $this->country = new Country($this);

        $this->country->exchangeArray($data);
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
