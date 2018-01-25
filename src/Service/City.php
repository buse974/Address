<?php

namespace Address\Service;

use Dal\Service\AbstractService;

class City extends AbstractService
{
    /**
     * Get list to city with filter.
     *
     * @param array $filter
     *
     * @return array
     */
    public function getList(array $filter = array())
    {
        $mapper = $this->getMapper();
        $res_city = $mapper->usePaginator($filter)->getList($filter);

        return [
            'count' => $mapper->count(),
            'results' => $res_city,
        ];
    }

    /**
     * Get division by name or id.
     *
     * @param array|string|int $city
     * @param array|string|int $division
     * @param array|string|int $country
     *
     * @return \Address\Model\City
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
     * Get city by id.
     *
     * @param int $city
     *
     * @return \Address\Model\City
     */
    public function getCityById($city)
    {
        return $this->getMapper()
            ->select($this->getModel()
            ->setId($city))
            ->current();
    }

    /**
     * Get city id.
     *
     * @param string           $city
     * @param array|string|int $division
     * @param array|string|int $country
     *
     * @return \Address\Model\City
     */
    public function getCityByName($city, $division = null, $country = null)
    {
        $country_id = null;
        if ($country) {
            $m_country = $this->getServiceCountry()->getCountry($country);
            if ($m_country !== null) {
                $country_id = $m_country->getId();
            }
        }

        $division_id = null;
        if ($division) {
            $m_division = $this->getServiceDivision()->getDivision($division, $country);
            if ($m_division !== null) {
                $division_id = $m_division->getId();
            }
        }

        $res_city = $this->getMapper()->getCityByName($city, $division_id, $country_id);

        return ($res_city->count() > 0) ? $res_city->current() : $this->add($city, $division_id, $country_id);
    }

    /**
     * Add new city.
     *
     * @param string           $city
     * @param array|string|int $country
     * @param array|string|int $division
     * @param string           $libelle
     * @param string           $state_long
     *
     * @return \Address\Model\City
     */
    public function add($city, $division = null, $country = null, $libelle = null, $code = null)
    {
        $country_id = null;
        $country_name = '';
        if ($country) {
            $m_country = $this->getServiceCountry()->getCountry($country);
            if ($m_country !== null) {
                $country_name = $m_country->getName();
                $country_id = $m_country->getId();
            }
        }

        $division_id = null;
        $division_name = '';
        if ($division) {
            $m_division = $this->getServiceDivision()->getDivision($division, $country);
            if ($m_division !== null) {
                $division_name = $m_division->getName();
                $division_id = $m_division->getId();
            }
        }

        $m_city = $this->getModel();
        $m_city->setName($city)
            ->setCountryId($country_id)
            ->setDivisionId($division_id)
            ->setLibelle($libelle)
            ->setCode($code);

        if (null !== ($LngLat = $this->getLngLat($city, $division_name, $country_name))) {
            $m_city->setLongitude($LngLat['lng'])->setLatitude($LngLat['lat']);
        }

        if ($this->getMapper()->insert($m_city) === 0) {
            throw new \Exception('Error: insert city');
        }

        return $m_city->setId($this->getMapper()
            ->getLastInsertValue());
    }

    /**
     * Update city.
     *
     * @param array $data
     *
     * @return int
     */
    public function update($data)
    {
        if (!isset($data['city']['id'])) {
            return;
        }

        $m_city = $this->getModel();
        $m_city->setId($data['city']['id']);

        if (isset($data['country'])) {
            $contry = $this->getServiceCountry()->getCountry($data['country']);
            $m_city->setCountryId(($contry) ? $contry->getId() : null);
        }
        if (isset($data['division'])) {
            $division = $this->getServiceDivision()->getDivision($data['division'], $m_city->getCountryId());
            $m_city->setDivisionId(($division) ? $division->getId() : null);
        }
        if (isset($data['city']['name'])) {
            $m_city->setName($data['city']['name']);
        }
        if (isset($data['city']['code'])) {
            $m_city->setCode($data['city']['code']);
        }
        if (isset($data['city']['libelle'])) {
            $m_city->setLibelle($data['city']['libelle']);
        }

        return $this->getMapper()->update($m_city);
    }

    /**
     * Delete city by Id.
     *
     * @param int $city
     *
     * @return int
     */
    public function delete($city)
    {
        return $this->getMapper()->delete($this->getModel()
            ->setId($city));
    }

    /**
     * Get lng and lat by city division country.
     *
     * @param string $city
     * @param string $division
     * @param string $country
     *
     * @return array
     */
    public function getLngLat($city, $division = '', $country = '')
    {
        return $this->container
            ->get('geoloc')
            ->getLngLat(sprintf('%s %s %s', $city, $division, $country));
    }

    /**
     * @return \Address\Service\Country
     */
    public function getServiceCountry()
    {
        return $this->container->get('addr_service_country');
    }

    /**
     * @return \Address\Service\Division
     */
    public function getServiceDivision()
    {
        return $this->container->get('addr_service_division');
    }
}
