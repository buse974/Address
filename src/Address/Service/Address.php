<?php

namespace Address\Service;

use Dal\Service\AbstractService;
use Address\Geoloc\Geoloc;

class Address extends AbstractService
{
    /**
     * Get address by name or id.
     *
     * @param array|string|int $address
     *
     * @return \Address\Model\Address
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
     * Get address by id.
     *
     * @param int $address
     *
     * @return \Address\Model\Address
     */
    public function getAddressById($address)
    {
        return $this->get($address);
    }

    /**
     * Get address by array values.
     *
     * @param array $address
     *
     * @return \Address\Model\Address
     */
    public function getAddressByArray(array $data)
    {
        $country_id = null;
        if (isset($data['country'])) {
            $m_country = $this->getServiceCountry()->getCountry($data['country']);
            if ($m_country !== null) {
                $country_id = $m_country->getId();
            }
        }
        $division_id = null;
        if (isset($data['division'])) {
            $m_division = $this->getServiceDivision()->getDivision($data['division'], $country_id);
            if ($m_division !== null) {
                $division_id = $m_division->getId();
            }
        }
        $city_id = null;
        if (isset($data['city'])) {
            $m_city = $this->getServiceCity()->getCity($data['city'], $division_id, $country_id);
            if ($m_city !== null) {
                $city_id = $m_city->getId();
            }
        }

        $res_address = $this->getMapper()->selectByArray($data, $city_id, $division_id, $country_id);

        if ($res_address->count() > 0) {
            $m_address = $res_address->current();
        } else {
            $street_no = (!empty($data['street_no'])) ? $data['street_no'] : null;
            $street_type = (!empty($data['street_type'])) ? $data['street_type'] : null;
            $street_name = (!empty($data['street_name'])) ? $data['street_name'] : null;
            $floor = (!empty($data['floor'])) ? $data['floor'] : null;
            $door = (!empty($data['door'])) ? $data['door'] : null;
            $apartment = (!empty($data['apartment'])) ? $data['apartment'] : null;
            $building = (!empty($data['building'])) ? $data['building'] : null;
            $lat = (!empty($data['latitude'])) ? $data['latitude'] : null;
            $lng = (!empty($data['longitude'])) ? $data['longitude'] : null;

            $m_address = $this->add($street_no, $street_type, $street_name, $floor, $door, $apartment, $building, $city_id, $division_id, $country_id, $lat, $lng);
        }

        return $m_address;
    }

    /**
     * @param unknown $street_no
     * @param unknown $street_type
     * @param unknown $street_name
     * @param unknown $floor
     * @param unknown $door
     * @param unknown $apartment
     * @param unknown $building
     * @param unknown $city
     * @param unknown $division
     * @param unknown $country
     * @param string  $lat
     * @param string  $lng
     *
     * @throws \Exception
     * 
     * @return \Dal\Model\AbstractModel
     */
    public function add($street_no, $street_type, $street_name, $floor, $door, $apartment, $building, $city = null, $division = null, $country = null, $lat = null, $lng = null)
    {
        $country_id = null;
        $country_name = '';
        if ($country) {
            $m_country = $this->getServiceCountry()->getCountry($country);
            if ($m_country !== null) {
                $country_id = $m_country->getId();
                $country_name = $m_country->getName();
            }
        }

        $division_id = null;
        $division_name = '';
        if ($division) {
            $m_division = $this->getServiceDivision()->getDivision($division, $country_id);
            if ($m_division !== null) {
                $division_id = $m_division->getId();
                $division_name = $m_division->getName();
            }
        }

        $city_id = null;
        $city_name = '';
        if ($city) {
            $m_city = $this->getServiceCity()->getCity($city, $division_id, $country_id);
            if ($m_city !== null) {
                $city_id = $m_city->getId();
                $city_name = $m_city->getName();
            }
        }

        $m_address = $this->getModel();
        $m_address->setStreetType($street_type)
            ->setStreetName($street_name)
            ->setStreetNo($street_no)
            ->setApartment($apartment)
            ->setFloor($floor)
            ->setDoor($door)
            ->setBuilding($building)
            ->setCityId($city_id)
            ->setDivisionId($division_id)
            ->setCountryId($country_id);

	if(!$m_address->toArrayCurrent()) {
	    return null;
	}
        $LngLat = ($lat && $lng) ? ['lat' => $lat,'lng' => $lng] : $this->getLngLat($street_no, $street_type, $street_name, $city_name, $division_name, $country_name);
        if (null !== $LngLat) {
            $m_address->setLongitude($LngLat['lng'])->setLatitude($LngLat['lat']);
            if (null !== ($tmz = $this->getServiceGeoloc()->getTimezone($LngLat['lat'], $LngLat['lng']))) {
                $m_address->setTimezone(($tmz) ? $tmz['timeZoneId'] : null);
            }
        }

        if ($this->getMapper()->insert($m_address) === 0) {
            throw new \Exception('Error: insert city');
        }

        $m_address->setId($this->getMapper()
            ->getLastInsertValue());

        return $m_address;
    }

    /**
     * Get Address By Id.
     *
     * @param int $address
     *
     * @return \Address\Model\Address\Relational
     */
    public function get($address)
    {
        $m_address = $this->getMapper()
            ->get($address)
            ->current();

        if (!is_double($m_address->getLatitude()) && !is_double($m_address->getLongitude())) {
            $this->updateLngLatTmz($m_address);
        }

        return $m_address;
    }

    public function initLngLat()
    {
        $res_addr = $this->getMapper()->getList();
        $ret = array();
        foreach ($res_addr as $m_address) {
            $ret[$m_address->getId()] = $this->updateLngLatTmz($m_address);
        }

        return $ret;
    }

    /**
     * @param \Address\Model\Address $m_address
     *
     * @return int
     */
    public function updateLngLatTmz($m_address)
    {
        $ret = 0;
        $result = $this->getLngLat($m_address->getStreetNo(), $m_address->getStreetType(), $m_address->getStreetName(), $m_address->getCity()->getName(), $m_address->getDivision()->getName(), $m_address->getCountry()->getName());
        if (null !== $result) {
            $m_address->setLatitude($result['lat'])->setLongitude($result['lng']);
            if (null !== ($tmz = $this->getServiceGeoloc()->getTimezone($result['lat'], $result['lng']))) {
                $m_address->setTimezone($tmz['timeZoneId']);
            }

            $ret = $this->getMapper()->update($m_address);
        }

        return $ret;
    }

    /**
     * Get lng and lat.
     *
     * @param string $street_no
     * @param string $street_type
     * @param string $street_name
     * @param string $city
     * @param string $division
     * @param string $country
     *
     * @return array
     */
    public function getLngLat($street_no, $street_type, $street_name, $city, $division, $country)
    {
        return $this->getServiceGeoloc()->getLngLat(sprintf('%s %s %s %s %s %s', $street_no, $street_type, $street_name, $city, $division, $country));
    }

    /**
     * @return \Address\Geoloc\Geoloc
     */
    public function getServiceGeoloc()
    {
        return $this->container->get('geoloc');
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

    /**
     * @return \Address\Service\City
     */
    public function getServiceCity()
    {
        return $this->container->get('addr_service_city');
    }
}
