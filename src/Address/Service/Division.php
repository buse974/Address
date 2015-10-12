<?php

namespace Address\Service;

use Dal\Service\AbstractService;

class Division extends AbstractService
{
	/**
     * Get list to division with filter
     * 
     * @param array $filter
     * 
     * @return \Dal\Db\ResultSet
     */
    public function getList(array $filter = array())
    {
    	$mapper = $this->getMapper();
    	$res_division = $mapper->usePaginator($filter)->getList($filter);
    	 
    	return array('count' =>  $mapper->count(), 'results' => $res_division);
    }

    /**
     * Get division by name or id
     *
     * @param array|string|integer $division
     * @param array|string|integer $country
     *      
     * @return \Address\Model\Division
     */
    public function getDivision($division, $country=null)
    {
    	$m_division = null;
    
    	if (is_array($division) && isset($division['id']) && is_numeric($division['id'])) {
    		$m_division = $this->getDivisionById($division['id']);
    	} elseif (is_numeric($division)) {
    		$m_division = $this->getDivisionById($division);
    	} elseif (is_array($division) && isset($division['name']) && !empty($division['name'])) {
    		$m_division = $this->getDivisionByName($division['name'], $country);
    	} elseif (is_string($division) && !empty($division)) {
    		$m_division = $this->getDivisionByName($division, $country);
    	}
    
    	return $m_division;
    }
    
    /**
     * Get division by id
     *
     * @param integer $division
     *
     * @return \Address\Model\Division
     */
    public function getDivisionById($division)
    {
    	return $this->getMapper()->select($this->getModel()->setId($division))->current();
    }
    
    /**
     * Get division by Name
     *
     * @param string $division
     * @param array|string|integer $country
     *     
     * @return \Address\Model\Division
     */
    public function getDivisionByName($division, $country = null)
    {
    	$country_id = null;
    	if ($country) {
        	$country_id =  $this->getServiceCountry()->getCountry($country)->getId();
        }
            
		$res_division = $this->getMapper()->getDivisionByName($division, $country_id);
            
		return ($res_division->count() > 0) ?
        	$res_division->current() :
        	$this->add($division, $country_id);

    }

    /**
     * Add new division
     * 
     * @param string $division
     * @param array|string|integer $country
     * @param string $short_name
     * @param string $libelle
     * @param integer $code
     * @throws \Exception
     * 
     * @return \Address\Model\Division
     */
    public function add($division, $country = null, $short_name = null, $libelle = null, $code = null)
    {
    	$country_name = '';
    	$country_id = null;
    	
    	if ($country) {
    		$m_country = $this->getServiceCountry()->getCountry($country);
    		if($m_country!==null) {
    			$country_name = $m_country->getName();
    			$country_id = $m_country->getId();
    		}
    	}
    	 
    	$LngLat = $this->getLngLat($division, $country_name);
    
    	$m_division = $this->getModel();
    	$m_division->setName($division)
    			   ->setShortName($short_name)
    			   ->setLibelle($libelle)
    	           ->setCode($code)
    	           ->setCountryId($country_id)
    	           ->setLongitude($LngLat['lng'])
    	           ->setLatitude($LngLat['lat']);
    	 
    	if ($this->getMapper()->insert($m_division) === 0) {
    		throw new \Exception('Error: insert city');
    	}
    	 
    	return $m_division->setId($this->getMapper()->getLastInsertValue());
    }
    
    /**
     * Get lng and lat by division country
     *
     * @param string $division
     * @param string $country
     *
     * @return array
     */
    public function getLngLat($division, $country = '')
    {
    	return $this->getServiceLocator()->get('geoloc')->getLngLat(sprintf('%s %s', $division, $country));
    }
    
    /**
     * @return \Address\Service\Country
     */
    public function getServiceCountry()
    {
        return $this->getServiceLocator()->get('addr_service_country');
    }

    /**
     * @return \Address\Geoloc\Geoloc
     */
    public function getServiceGeoloc()
    {
        return $this->getServiceLocator()->get('geoloc');
    }
}
