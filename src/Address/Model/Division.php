<?php

namespace Address\Model;

use Address\Model\Base\Division as BaseDivision;

class Division extends BaseDivision
{
    protected $country;

    public function exchangeArray(array &$data)
    {
        parent::exchangeArray($data);

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
}
