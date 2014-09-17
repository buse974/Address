<?php

namespace Address\Service;

use Address\Model\City as ModelCity;
use Dal\Service\AbstractService;

class City extends AbstractService
{
    /**
     *
     * @param  String $name
     * @param  int    $country_id
     * @param  String $libelle
     * @param  String $state_short
     * @param  String $state_long
     * @param  String $latitude
     * @param  String $longitude
     * @return type
     */
    public function add($name, $country, $division=null, $libelle=null,$code=null,$latitude=null,$longitude=null)
    {
        $country_id = null;
        $division_id = null;
        if ($country) {
            $country = $this->getServiceCountry()->getCountry($country);
            if ($country) {
                $country_id = $country->getId();
            }
        }
        $division_id = null;
        if ($division) {
            $division = $this->getServiceDivision()->getDivision($division,$country_id);
            if ($division) {
                $division_id = $division->getId();
            }
        }

        $cityObj = new ModelCity();
        $cityObj->setCode($code)
                ->setLatitude($latitude)
                ->setLibelle($libelle)
                ->setLongitude($longitude)
                ->setDivisionId($division_id)
                ->setCountryId($country_id)
                ->setName($name);

        return $this->getMapper()->insert($cityObj);
    }

    public function update($datas)
    {
        if (!isset($datas['city']['id'])) {
            return;
        }

        $country_id = null;
        if (!empty($datas['country'])) {
            $country = $this->getServiceCountry()->getCountry($datas['country']);
            if ($country) {
            $country_id = $country->getId();
            }
        }
        $division_id = null;
        if (!empty($datas['division'])) {
            $division = $this->getServiceDivision()->getDivision($datas['division'],$country_id);
            if ($division) {
            $division_id = $division->getId();
            }
        }

        $mcity = new ModelCity();
        $mcity->setId(isset($datas['city']['id']) ? $datas['city']['id']:null)
              ->setName(isset($datas['city']['name']) ? $datas['city']['name']:null)
              ->setCode(isset($datas['city']['code']) ? $datas['city']['code']:null)
              ->setLibelle(isset($datas['city']['libelle']) ? $datas['city']['libelle']:null)
              ->setLatitude(isset($datas['city']['latitude']) ? $datas['city']['latitude']:null)
              ->setLongitude(isset($datas['city']['longitude']) ? $datas['city']['longitude']:null)
              ->setCountryId($country_id)
              ->setDivisionId($division_id);

      return $this->getMapper()->update($mcity);
    }

    public function delete($city)
    {
        $mcity = new ModelCity();
        $mcity->setId($city);

        return $this->getMapper()->delete($mcity);
    }

    /**
     * Get cities (for autocompletion)
     *
     * @param  array $filter
     * @return array
     */
    public function getList($filter)
    {
        $mapper = $this->getMapper()->checkUsePagination($filter);
        $list = $mapper->getList($filter);

        if (!empty($filter['count']) && $filter['count'] === true) {
            return array('count' =>  $mapper->count(), 'list'=>$list);
        } else {
            return $list;
        }
    }

    /**
     * Get city id
     *
     * @param  string          $city_name
     * @param  string          $country_name
     * @return \Dal\Model\City | null
     */
    public function getCity($city,$state = null ,$country = null)
    {
        $addr = null;
        $city_obj = null;
        $city_id = null;
        $city_name = null;
        $country_id = null;
        $state_id = null;

        if (is_array($city) && isset($city['id']) && is_numeric($city['id'])) {
            $city_id = $city['id'];
        } elseif (is_numeric($city)) {
            $city_id = $city;
        } elseif (is_array($city) && isset($city['name']) && !empty($city['name'])) {
            $city_name = $city['name'];
        } elseif (!empty($city) && is_string($city)) {
            $city_name = $city;
        }

        if ($city_id) {
            $city_obj = $this->getMapper()->select((new ModelCity())->setId($city_id))->current();
        } elseif ($city_name) {
            if ($country) {
                $country = $this->getServiceCountry()->getCountry($country);
                if ($country) {
                $country_id = $country->getId();
                $addr .= $country->getName() . ' ';
                }
            }
            if ($state) {
                $state = $this->getServiceAddrState()->getState($state);
                if ($state) {
                $state_id = $state->getId();
                $addr .= $state->getName() . ' ';
                }
            }
            $resultSet = $this->getMapper()->getCityId($city_name, $state_id, $country_id);
            if ($resultSet->count() > 0) {
                $city_obj = $resultSet->current();
            } else {
                $mCity = new ModelCity();
                $mCity->setCountryId($country_id)
                      ->setDivisionId($state_id)
                      ->setName($city_name);
                if ($addr) {
                    $result = $this->getServiceGeoloc()->getGeoloc($addr . ' ' . $city_name);
                    $result = $result['results'][0]['geometry']['location'];
                    
                    $mCity->setLatitude($result['lat'])
                          ->setLongitude($result['lng']);
                }
                if ($this->getMapper()->insert($mCity)) {
                    $city_obj = $mCity->setId($this->getMapper()->getLastInsertValue());
                }
            }
        }

        return $city_obj;
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
     * @return \Dal\Geoloc\Geoloc
     */
    public function getServiceGeoloc()
    {
        return $this->getServiceLocator()->get('geoloc');
    }
}
