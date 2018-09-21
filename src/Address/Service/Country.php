<?php

namespace Address\Service;

use Dal\Service\AbstractService;

class Country extends AbstractService
{
    /**
     * Get list to conutry with filter.
     *
     * @param array $filter
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function getList(array $filter = array())
    {
        $mapper = $this->getMapper();
        $res_country = $mapper->usePaginator($filter)->getList($filter);

        return array('count' => $mapper->count(),'results' => $res_country);
    }

    /**
     * Get country by name or id.
     *
     * @param array|string|int $country
     *
     * @return \Address\Model\Country
     */
    public function getCountry($country)
    {
        $m_country = null;

        if (is_array($country) && isset($country['id']) && is_numeric($country['id'])) {
            $m_country = $this->getCountryById($country['id']);
        } elseif (is_numeric($country)) {
            $m_country = $this->getCountryById($country);
        } elseif (is_array($country) && isset($country['name']) && !empty($country['name'])) {
            $m_country = $this->getCountryByName($country['name']);
        } elseif (is_string($country) && !empty($country)) {
            $m_country = $this->getCountryByName($country);
        }

        return $m_country;
    }

    /**
     * Get country by id.
     *
     * @param int $country
     *
     * @return \Address\Model\Country
     */
    public function getCountryById($country)
    {
        return $this->getMapper()
            ->select($this->getModel()
            ->setId($country))
            ->current();
    }

    /**
     * Get conutry by Name.
     *
     * @param string $country
     *
     * @return \Address\Model\Country
     */
    public function getCountryByName($country)
    {
        $res_country = $this->getMapper()->getCountryByName($country);

        if ($res_country->count() > 0) {
            $m_country = $res_country->current();
        } else {
            $m_country = $this->getModel()->setName($country);

            if (null !== ($LngLat = $this->getLngLat($country))) {
                $m_country->setLongitude($LngLat['lng'])->setLatitude($LngLat['lat']);
            }
            if ($this->getMapper()->insert($m_country) === 0) {
                throw new \Exception('Error: insert country');
            }

            $m_country->setId($this->getMapper()
                ->getLastInsertValue());
        }

        return $m_country;
    }

    /**
     * Get lng and lat by country.
     *          
     * @param string $country
     *
     * @return array
     */
    public function getLngLat($country)
    {
        return $this->container
            ->get('geoloc')
            ->getLngLat($country);
    }
}
