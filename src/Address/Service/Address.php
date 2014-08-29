<?php

namespace Address\Service;

use Address\Model\Address as ModelAddress;
use Zend\Db\Sql\Predicate\IsNull;

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
                $result = $this->getServiceGeoloc()->getGeoloc(((!empty($datas['street_type']))?$datas['street_type'] . " ":"") . ((!empty($datas['street_name']))?$datas['street_name'] . " ":"") . ((!empty($datas['street_no']))?$datas['street_no'] . " ":"") . $city_name . $state_name . ((!empty($country_name))? " ," . $country_name:""));
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
     * @param  number                 $address
     * @return \Address\Model\Address
     */
    public function get($address)
    {
        $res = $this->getMapper()->get($address)->current();
        
        if(!is_double($res->getLatitude()) && !is_double($res->getLongitude())) {
        	$res = $res->toArray();
        	$results = $this->getServiceGeoloc()->getGeoloc(((!empty($res['street_type']))?$res['street_type'] . " ":"") . ((!empty($res['street_name']))?$res['street_name'] . " ":"") . ((!empty($res['street_no']))?$res['street_no'] . " ":"") . $res['city']['name'] . $res['division']['name'] . ((!empty($res['country']['name']))? " ," . $res['city']['name']:""));
        	
        	if($results['status'] === 'OK'){
        		$result = $results['results'][0]['geometry']['location'];
        		$tmz = $this->getServiceGeoloc()->getTimezone($result['lat'], $result['lng']);
        		 
        		$m_address = new ModelAddress();
        		$m_address->setId($res['id'])
		        		  ->setLatitude($result['lat'])
		        		  ->setLongitude($result['lng'])
		        		  ->setTimezone($tmz['timeZoneId']);
        		 
        		$res['latitude']  = $result['lat'];
		        $res['longitude'] = $result['lng'];
		        $res['timezone']  = $tmz['timeZoneId'];
        		
        		$this->getMapper()->update($m_address) . "\n\n";
        	}
        }
        	
        return $res;
    }
    
    public function initLngLat()
    {
    	$res_addr = $this->getMapper()->getList();
    	$ret = array();
    	foreach ($res_addr as $addr){
    		$datas = $addr->toArray();
    		$results = $this->getServiceGeoloc()->getGeoloc(((!empty($datas['street_type']))?$datas['street_type'] . " ":"") . ((!empty($datas['street_name']))?$datas['street_name'] . " ":"") . ((!empty($datas['street_no']))?$datas['street_no'] . " ":"") . $datas['city']['name'] . $datas['division']['name'] . ((!empty($datas['country']['name']))? " ," . $datas['city']['name']:""));
    		
    		if($results['status'] === 'OK'){
	    		$result = $results['results'][0]['geometry']['location'];
	    		
	    		$tmz = $this->getServiceGeoloc()->getTimezone($result['lat'], $result['lng']);
	    		
	    		$m_address = new ModelAddress();
	    		$m_address->setId($datas['id'])
			    		  ->setLatitude($result['lat'])
			    		  ->setLongitude($result['lng'])
			    		  ->setTimezone($tmz['timeZoneId']);
	    		
	    		 $this->getMapper()->update($m_address);
    		}
    		
    		
    		$ret[$datas['id']] = $results['status'];
    	}
    	
    	return $ret;
    }

    /**
     * @return \Dal\Service\Country
     */
    public function getServiceCountry()
    {
        return $this->getServiceLocator()->get('addr_service_country');
    }

    /**
     * @return \Dal\Service\Division
     */
    public function getServiceDivision()
    {
        return $this->getServiceLocator()->get('addr_service_division');
    }

    /**
     * @return \Dal\Service\City
     */
    public function getServiceCity()
    {
        return $this->getServiceLocator()->get('addr_service_city');
    }

    /**
     * @return \Dal\Geoloc\Geoloc
     */
    public function getServiceGeoloc()
    {
        return $this->getServiceLocator()->get('geoloc');
    }
}
