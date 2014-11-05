<?php

namespace Address\Service;

use Address\Model\Address as ModelAddress;
use Zend\Db\Sql\Predicate\IsNull;
use Dal\Service\AbstractService;
use Address\Geoloc\Geoloc;

class Address extends AbstractService
{
    public function getAddressId(array $datas)
    {
        $id=null;
        $addr = $country_id = $country_name = $state_id = $state_name = $city_id = $city_name = null;

        if (!empty($datas['country'])) {
            $country = $this->getServiceCountry()->getCountry($datas['country']);
            if ($country) {
                $country_id = $country->getId();
                $country_name = $country->getShortName();
            }
        }
        if (!empty($datas['division'])) {
            $state = $this->getServiceDivision()->getDivision($datas['division'], $country_id);
            if ($state) {
                $state_id = $state->getId();
                $state_name = $state->getName();
            }
        }
        if (!empty($datas['city'])) {
            $city = $this->getServiceCity()->getCity($datas['city'], $state_id, $country_id);
            if ($city) {
                $city_id = $city->getId();
                $city_name = $city->getName();
            }
        }
        if ($country_id || $state_id || $city_id || !empty($datas['street_name'])) {
            $m_address = new ModelAddress();
            $m_address->setStreetType((!empty($datas['street_type']))?$datas['street_type']:new IsNull())
                      ->setStreetName((!empty($datas['street_name']))?$datas['street_name']:new IsNull())
                      ->setStreetNo((!empty($datas['street_no']))?$datas['street_no']:new IsNull())
                      ->setApartment((!empty($datas['apartment']))?$datas['apartment']:new IsNull())
                      ->setFloor((!empty($datas['floor']))?$datas['floor']:new IsNull())
                      ->setDoor((!empty($datas['door']))?$datas['door']:new IsNull())
                      ->setBuilding((!empty($datas['building']))?$datas['building']:new IsNull())
                      ->setCityId($city_id)
                      ->setDivisionId($state_id)
                      ->setCountryId($country_id);

            $resAddress = $this->getMapper()->select($m_address);

            if ($resAddress->count() > 0) {
                $id = $resAddress->current()->getId();
            } else {
            	$result = $this->getServiceGeoloc()->getGeoloc(
            			((!empty($datas['street_no']))?$datas['street_no'] . " ":"") .
            			((!empty($datas['street_type']))?$datas['street_type'] . " ":"") . 
            			((!empty($datas['street_name']))?$datas['street_name'] . " ":"") . 
            			$city_name . " " .
            			$state_name . 
            			((!empty($country_name))? " ," . $country_name:""));
                $result = $result['results'][0]['geometry']['location'];
                
                $tmz = $this->getServiceGeoloc()->getTimezone($result['lat'], $result['lng']);

                $m_address->setLatitude($result['lat'])
                          ->setLongitude($result['lng'])
                          ->setTimezone($tmz['timeZoneId']);
                
                if ($this->getMapper()->insert($m_address)) {
                    $id = $this->getMapper()->getLastInsertValue();
                }
            }
        }

        return $id;
    }

    /**
     *
     * @param  number	$address
     * @return \Address\Model\Address\Relational
     */
    public function get($address)
    {
    	$m_address = $this->getMapper()->get($address)->current();
    
    	if(!is_double($m_address->getLatitude()) && !is_double($m_address->getLongitude())) {
    		$this->updateLngLatTmz($m_address);
    	}
    
    	return $m_address;
    }
    
    public function initLngLat()
    {
    	$res_addr = $this->getMapper()->getList();
    	$ret = array();
    	foreach ($res_addr as $m_address){
    		$ret[$datas['id']] = $this->updateLngLatTmz($m_address);
    	}
    	
    	return $ret;
    }

    public function updateLngLatTmz($m_address) 
    {
    	$ret = null;
    	$addr_str = sprintf('%s %s %s %s %s %s',
    			((!$m_address->getStreetNo() instanceof IsNull) ? $m_address->getStreetNo() : ''),
    			((!$m_address->getStreetType() instanceof IsNull) ? $m_address->getStreetType() : ''),
    			((!$m_address->getStreetName() instanceof IsNull) ? $m_address->getStreetName() : ''),
    			((!$m_address->getCity()->getName() instanceof IsNull) ? $m_address->getCity()->getName() : ''),
    			((!$m_address->getDivision()->getName() instanceof IsNull) ? $m_address->getDivision()->getName() : ''),
    			((!$m_address->getCountry()->getName() instanceof IsNull) ? ','.$m_address->getCountry()->getName() : ''));
    	
    	$results = $this->getServiceGeoloc()->getGeoloc($addr_str);
    	 
    	if($results['status'] === Geoloc::STATUS_OK){
    		$result = $results['results'][0]['geometry']['location'];
    		$tmz = $this->getServiceGeoloc()->getTimezone($result['lat'], $result['lng']);
    	
    		$m_address->setLatitude($result['lat'])
    		->setLongitude($result['lng'])
    		->setTimezone($tmz['timeZoneId']);
    	
    		$this->getMapper()->update($m_address);
    		
    		$ret = $results['status'];
    	}
    	
    	return $ret;
    }
    /**
     * @return \Address\Service\Country
     */
    public function getServiceCountry()
    {
        return $this->getServiceLocator()->get('addr_service_country');
    }

    /**
     * @return \Address\Service\Division
     */
    public function getServiceDivision()
    {
        return $this->getServiceLocator()->get('addr_service_division');
    }

    /**
     * @return \Address\Service\City
     */
    public function getServiceCity()
    {
        return $this->getServiceLocator()->get('addr_service_city');
    }

    /**
     * @return \Address\Geoloc\Geoloc
     */
    public function getServiceGeoloc()
    {
        return $this->getServiceLocator()->get('geoloc');
    }
}
