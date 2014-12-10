<?php

namespace Address\Service;

use Zend\Db\Sql\Predicate\IsNull;
use Dal\Service\AbstractService;
use Address\Geoloc\Geoloc;

class Address extends AbstractService
{
	/**
	 * Get address by name or id
	 *
	 * @param array|string|integer $address
	 *
	 * @return \Address\Model\Address|null
	 */
	public function getAddress($address)
	{
		$m_address = null;
		 
		if (is_array($address) && isset($address['id']) && is_numeric($address['id'])) {
			$m_address = $this->getAddressById($address['id']);
		} elseif (is_numeric($address)) {
			$m_address = $this->getAddressById($address);
		} elseif (is_array($address)) {
			$m_address = $this->getAddressByArray($address);
		}
	
		return $m_address;
	}
	
    /**
     * Get address by id
     * 
     * @param integer $address
     * 
     * @return \Address\Model\Address|null
     */
    public function getAddressById($address)
    {
    	return $this->getMapper()->select($this->getModel()->setId($address))->current();
    }
    
    /**
     * Get address by array values
     *
     * @param array $address
     *
     * @return \Address\Model\Address|null
     */
    public function getAddressByArray(array $datas)
    {
        $country_id = null;
        $country_name='';
        if (isset($datas['country'])) {
            $m_country = $this->getServiceCountry()->getCountry($datas['country']);
            $country_id = $m_country->getId();
            $country_name = $m_country->getShortName(); 
        }
        
        $division_id = null;
        $division_name= '';
        if (isset($datas['division'])) {
            $m_division = $this->getServiceDivision()->getDivision($datas['division'], $country_id);
            $division_id = $m_division->getId();
            $division_name = $m_division->getName();
        }
        
        $city_id = null;
        $city_name = '';
        if (isset($datas['city'])) {
            $city = $this->getServiceCity()->getCity($datas['city'], $state_id, $country_id);
            $city_id = $city->getId();
            $city_name = $city->getName();
        }
        
        $m_address = $this->getModel();
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
                  
        $res_address = $this->getMapper()->select($m_address);

		if ($res_address->count() > 0) {
			$m_address = $resAddress->current();
		} else {
			$LngLat = $this->getLngLat((!empty($datas['street_name']))?$datas['street_name']:'',
					         (!empty($datas['street_no']))?$datas['street_no']:'',
					         (!empty($datas['street_type']))?$datas['street_type']:'',
							 $city_name,
							 $division_name,
					         (!empty($country_name))? $country_name:''
    		);
			
            $tmz = $this->getServiceGeoloc()->getTimezone($LngLat['lat'], $LngLat['lng']);

            $m_address->setLongitude($LngLat['lng'])
    		          ->setLatitude($LngLat['lat'])
                      ->setTimezone($tmz['timeZoneId']);
                
            if ($this->getMapper()->insert($m_address) === 0) {
            	throw new \Exception('Error: insert city');
            }
                
			$m_address->setId($this->getMapper()->getLastInsertValue());
		}
        
        return $m_address;
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
     * Get lng and lat by city division country
     *
     * @param stirng $city
     * @param string $division
     * @param string $country
     *
     * @return array
     */
    public function getLngLat($street_name, $street_no, $street_type, $city, $division, $country)
    {
    	return $this->getServiceLocator()->get('geoloc')->getLngLat(sprintf('%s %s %s %s %s %s', $street_no, $street_type, $street_name, $city, $division, $country));
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
