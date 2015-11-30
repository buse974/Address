<?php

namespace Address\Geoloc;

use Zend\Http\Client;

class Geoloc implements \Zend\ServiceManager\ServiceLocatorAwareInterface
{
    const STATUS_OK = 'OK';

    const STATUS_ZERO_RESULTS = 'ZERO_RESULTS';

    const STATUS_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';

    const STATUS_REQUEST_DENIED = 'REQUEST_DENIED';

    const STATUS_INVALID_REQUEST = 'INVALID_REQUEST';

    const STATUS_UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    protected $serviceLocator;

    protected $api_location = 'geocode';

    protected $api_timezone = 'timezone';

    protected $params_location = array('sensor' => 'false','address' => null);

    protected $params_timezone = array('location' => null,'timestamp' => 0);

    protected $output_format = 'json';

    /**
     * Get lng lat by address.
     *
     * @param string $address
     *
     * @return array
     */
    public function getLngLat($address)
    {
        $result = $this->send($this->getUrlApiLocation($address));

        if ($result === null || $result['status'] !== self::STATUS_OK) {
            return;
        }

        return $result['results'][0]['geometry']['location'];
    }

    /**
     * Get Timezone.
     *
     * @param float $latitude
     * @param float $longitude
     *
     * @return array
     */
    public function getTimezone($latitude, $longitude)
    {
        $result = $this->send($this->getUrlApiTimezone($latitude, $longitude));

        if ($result === null || $result['status'] !== self::STATUS_OK) {
            return;
        }

        return $result;
    }

    /**
     * Convert array to string url.
     *
     * @param array $params
     *
     * @return string
     */
    private function arrayToString($params)
    {
        $str_params = array();
        foreach ($params as $key => $value) {
            $str_params[] = $key.'='.$value;
        }

        return implode('&', $str_params);
    }

    /**
     * Get url api google geocode.
     *
     * @return string
     */
    private function getUrlApiLocation($address)
    {
        $this->params_location['address'] = urlencode($address);

        return $this->getServiceLocator()->get('config')['address-conf']['geoloc']['url'].$this->api_location.'/'.$this->output_format.'?'.$this->arrayToString($this->params_location);
    }

    /**
     * Get url api google timezone.
     *
     * @return string
     */
    private function getUrlApiTimezone($latitude, $longitude)
    {
        $this->params_timezone['location'] = sprintf('%s,%s', $latitude, $longitude);

        return $this->getServiceLocator()->get('config')['address-conf']['geoloc']['url'].$this->api_timezone.'/'.$this->output_format.'?'.$this->arrayToString($this->params_timezone);
    }

    /**
     * Set service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        if ($this->serviceLocator === null) {
            $this->serviceLocator = $serviceLocator;
        }

        return $this;
    }

    /**
     * Get service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function send($url)
    {
        $conf = $this->getServiceLocator()->get('config');
        $conf_addr = $conf['address-conf'];
        $cli = $this->getClient();
        $cli->setOptions($conf[$conf_addr['geoloc']['adapter']]);
        $cli->setUri($url);
        $ret = $cli->send();

        if ($ret->isClientError()) {
            return;
        }

        return json_decode($ret->getBody(), true);
    }

    public function getClient()
    {
        return new Client();
    }
}
