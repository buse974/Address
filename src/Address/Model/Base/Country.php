<?php

namespace Address\Model\base;

use Dal\Model\AbstractModel;

class Country extends AbstractModel
{
    protected $id;
    protected $iso2;
    protected $short_name;
    protected $name;
    protected $iso3;
    protected $numcode;
    protected $calling_code;
    protected $latitude;
    protected $longitude;

    protected $prefix = 'country';

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getIso2()
    {
        return $this->iso2;
    }

    public function setIso2($iso2)
    {
        $this->iso2 = $iso2;

        return $this;
    }

    public function getShortName()
    {
        return $this->short_name;
    }

    public function setShortName($short_name)
    {
        $this->short_name = $short_name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getIso3()
    {
        return $this->iso3;
    }

    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;

        return $this;
    }

    public function getNumcode()
    {
        return $this->numcode;
    }

    public function setNumcode($numcode)
    {
        $this->numcode = $numcode;

        return $this;
    }

    public function getCallingCode()
    {
        return $this->calling_code;
    }

    public function setCallingCode($calling_code)
    {
        $this->calling_code = $calling_code;

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

}
