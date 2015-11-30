<?php

namespace Address\Model;

use Address\Model\Base\City as BaseCity;

class City extends BaseCity
{
    protected $division;
    protected $country;

    public function exchangeArray(array &$data)
    {
        parent::exchangeArray($data);

        $this->division = $this->requireModel('addr_model_division', $data);
        $this->country = $this->requireModel('addr_model_country', $data);
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
