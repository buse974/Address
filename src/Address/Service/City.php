<?php

namespace Address\Service;

use Dal\Service\AbstractService;

class City extends AbstractService
{
	/**
     * Get list to city with filter
     * 
     * @param array $filter
     * 
     * @return \Dal\Db\ResultSet
     */
    public function getList(array $filter = array())
    {
        $mapper = $this->getMapper();
        $res_city = $mapper->usePaginator($filter)->getList($filter);
        
    	return array('count' =>  $mapper->count(), 'results' => $res_city);
    }

    /**
     * Get division by name or id
     * 
     * @param array|string|integer $city
     * @param array|string|integer|null $division
     * @param array|string|integer|null $country
     * 
     * @return \Address\Model\City|null
     */
    public function getCity($city, $division = null, $country = null)
    {
    	$m_city = null;
    
    	if (is_array($city) && isset($city['id']) && is_numeric($city['id'])) {
    		$m_city = $this->getCityById($city['id']);
    	} elseif (is_numeric($city)) {
    		$m_city = $this->getCityById($city);
    	} elseif (is_array($city) && isset($city['name']) && !empty($city['name'])) {
    		$m_city = $this->getCityByName($city['name'], $division, $country);
    	} elseif (is_string($city) && !empty($city)) {
    		$m_city = $this->getCityByName($city, $division, $country);
    	}
    
    	return $m_city;
    }
    
    /**
     * Get city by id
     * 
     * @param  integer $city
     * 
     * @return \Dal\Model\City|null
     */
    public function getCityById($city)
    {
		return  $this->getMapper()->select($this->getModel()->setId($city))->current();
    }
    
    /**
     * Get city id
     * 
     * @param string $city
     * @param array|string|integer|null $division
     * @param array|string|integer|null $country
     *
     * @return \Dal\Model\City|null
     */
    public function getCityByName($city, $division = null, $country = null)
    {
    	$m_city = null;
    	
    	$country_id = null;
    	if ($country) {
    		$country_id = $this->getServiceCountry()->getCountry($country)->getId();
    	}
    	
    	$division_id = null;
    	if ($division) {
    		$division_id = $this->getServiceDivision()->getDivision($division, $country)->getId();
    	}
    	
    	$res_city = $this->getMapper()->getCityName($city, $division_id, $country_id);
    	
    	if ($res_city->count() > 0) {
    		$m_city = $res_city->current();
    	} else {
    		$m_city = $this->add($city, $division_id, $country_id);
    	}
    	
    	return $m_city;
    }

    /**
     * Add new city
     * 
     * @param  string $city
     * @param  array|string|integer|null $country
     * @param  array|string|integer|null $division
     * @param  string|null $libelle
     * @param  string|null $state_long
     * 
     * @return \Address\Model\City
     */
    public function add($city, $division = null, $country = null, $libelle=null, $code=null)
    {
    	$country_name = '';
    	$division_name = '';
    	$country_id = null;
    	$division_id = null;
    	
    	if ($country) {
    		$m_country = $this->getServiceCountry()->getCountry($country);
    		$country_name = $m_country->getName();
    		$country_id   = $m_country->getId();
    	}
    	
    	if ($division) {
    		$m_division = $this->getServiceDivision()->getDivision($division, $country);
    		$division_name = $m_division->getName();
    		$division_id   = $m_division->getId();
    	}
    	
    	$LngLat = $this->getLngLat($city, $division_name, $country_name);

		$m_city = $this->getModel();
    	$m_city->setName($city)
    		   ->setCountryId($country_id)
    		   ->setDivisionId($division_id)
    		   ->setLibelle($libelle)
    		   ->setCode($code)
    		   ->setLongitude($LngLat['lng'])
        	   ->setLatitude($LngLat['lat']);
    			
		if ($this->getMapper()->insert($m_division) === 0) {
			throw new \Exception('Error: insert city');
    	}
    	
    	return $m_city->setId($this->getMapper()->getLastInsertValue());
    }
    
    /**
     * Update city
     * 
     * @param array $datas
     * @return integer
     */
    public function update($datas)
    {
    	if (!isset($datas['city']['id'])) {
    		return;
    	}
    
    	$country_id = null;
    	if (isset($datas['country'])) {
    		$m_country = $this->getServiceCountry()->getCountry($datas['country']);
    		$country_id = $m_country->getId();
    	}
    	$division_id = null;
    	if (isset($datas['division'])) {
    		$m_division = $this->getServiceDivision()->getDivision($datas['division'],$country_id);
    		$division_id = $m_division->getId();
    	}

    	$m_city = $this->getModel();
    	$m_city->setId($datas['city']['id'])
    	       ->setName(isset($datas['city']['name']) ? $datas['city']['name']:null)
    	       ->setCode(isset($datas['city']['code']) ? $datas['city']['code']:null)
    	       ->setLibelle(isset($datas['city']['libelle']) ? $datas['city']['libelle']:null)
    	       ->setCountryId($country_id)
    	       ->setDivisionId($division_id);
    
    	return $this->getMapper()->update($m_city);
    }
    
    /**
     * Delete city by Id
     * 
     * @param integer $city
     * 
     * @return integer
     */
    public function delete($city)
    {
    	return $this->getMapper()->delete($this->getModel()->setId($city));
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
    public function getLngLat($city, $division = '', $country = '')
    {
    	return $this->getServiceLocator()->get('geoloc')->getLngLat(sprintf('%s %s %s',$city, $division, $country));
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
}
