<?php

namespace Address\Service;

use Address\Model\Division as ModelDivision;
use Dal\Service\AbstractService;

class Division extends AbstractService
{
    public function getList($filter)
    {
        return $this->getMapper()->checkUsePagination($filter)->getList($filter);
    }

    public function getId($name,$country_id,$short_name=null)
    {
        $id=null;
        $mstate = new ModelDivision();
        $mstate->setName($name)
               ->setCountryId($country_id);

        if ($short_name) {
            $mstate->setShortName($short_name);
        }

        $ret =  $this->getMapper()->select($mstate);

        if ($ret->count()==0) {
            if ($this->getMapper()->insert($mstate)) {
                $id = $this->getMapper()->getLastInsertValue();
            }
        } else {
                $id = $ret->current()->getId();
        }

        return $id;
    }

    public function getDivision($state, $country=null)
    {
        $addr = null;
        $state_obj = null;
        $state_id = null;
        $state_name = null;
        $country_id = null;

        if (is_array($state) && isset($state['id']) && is_numeric($state['id'])) {
            $state_id = $state['id'];
        } elseif (is_numeric($state)) {
            $state_id = $state;
        } elseif (is_array($state) && isset($state['name']) && !empty($state['name'])) {
            $state_name = $state['name'];
        } elseif (!empty($state) && is_string($state)) {
            $state_name = $state;
        }

        if ($state_id) {
            $state_obj = $this->getMapper()->select((new ModelDivision())->setId($state_id))->current();
        } elseif ($state_name) {
            if ($country) {
                $country_obj =  $this->getServiceCountry()->getCountry($country);
                $country_id = $country_obj->getId();
                $addr .= $country_obj->getName();
            }
            $resultSet = $this->getMapper()->getStateId($state_name, $country_id);
            if ($resultSet->count() > 0) {
                $state_obj = $resultSet->current();
            } else {
                $mSate = new ModelDivision();
                $mSate->setCountryId($country_id)
                      ->setName($state_name);

                if ($addr) {
                    $result = $this->getServiceGeoloc()->getGeoloc($state_name . ' ' . $addr);
                    $result = $result['results'][0]['geometry']['location'];
                    
                    $mSate->setLongitude($result['lng'])
                    	  ->setLatitude($result['lat']);
                }
                if ($this->getMapper()->insert($mSate)) {
                    $state_obj = $mSate->setId($this->getMapper()->getLastInsertValue());
                }
            }
        }

        return $state_obj;
    }

    /**
     * @return \Dal\Service\Country
     */
    public function getServiceCountry()
    {
        return $this->getServiceLocator()->get('addr_service_country');
    }

    /**
     * @return \Dal\Geoloc\Geoloc
     */
    public function getServiceGeoloc()
    {
        return $this->getServiceLocator()->get('geoloc');
    }
}
