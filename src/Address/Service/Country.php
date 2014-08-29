<?php

namespace Address\Service;

use Address\Model\Country as ModelCountry;

class Country extends AbstractService
{
    public function getAllCountry()
    {
        return $this->getMapper()->getAllCountry();
    }

    public function getList($filter)
    {
        $mapper = $this->getMapper()->checkUsePagination($filter);

        return $mapper->getList($filter);
    }

    public function getCountry($country)
    {
        $country_id = null;
        $country_name = null;
        $country_obj = null;

        if (is_array($country) && isset($country['id']) && is_numeric($country['id'])) {
            $country_id = $country['id'];
        } elseif (is_numeric($country)) {
            $country_id = $country;
        } elseif (is_array($country) && isset($country['name']) && !empty($country['name'])) {
            $country_name = $country['name'];
        } elseif (!empty($country) && is_string($country)) {
            $country_name = $country;
        }

        if ($country_id) {
            $country_obj = $this->getMapper()->select((new ModelCountry())->setId($country_id))->current();
        } elseif ($country_name) {
            $resCountry = $this->getMapper()->getCountryId($country_name);
            if ($resCountry->count() > 0) {
                $country_obj = $resCountry->current();
            } else {
                $result = $this->getServiceGeoloc()->getGeoloc($country_name);
                $result = $result['results'][0]['geometry']['location'];
                
                $modelcountry = new ModelCountry();
                $modelcountry->setName($country_name)
                             ->setLongitude($result['lng'])
                             ->setLatitude($result['lat']);
                
                if ($this->getMapper()->insert($modelcountry)) {
                    $country_obj = $modelcountry->setId($this->getMapper()->getLastInsertValue());
                }
            }
        }

        return $country_obj;
    }

    /**
     * @return \Dal\Mapper\Country
     */
    public function getMapper()
    {
        return $this->getServiceLocator()->get($this->mapper);
    }

    /**
     * @return \Dal\Geoloc\Geoloc
     */
    public function getServiceGeoloc()
    {
        return $this->getServiceLocator()->get('geoloc');
    }
}
