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
     * Get array geoloc google API
     *
     * @param string $address            
     * @throws \Exception
     *
     * @return array
     */
    public function getGeoloc($address)
    {
        $this->params_location['address'] = urlencode($address);
        
        $conf = $this->getServiceLocator()->get('config');
        $conf_addr = $conf['address-conf'];
        $cli = new Client();
        $cli->setOptions($conf[$conf_addr['geoloc']['adapter']]);
        $cli->setUri($this->getUrlApiLocation());
        $ret = $cli->send();
        
        if ($ret->isClientError()) {
            throw new \Exception($ret->getReasonPhrase(), $ret->getStatusCode());
        }
        
        $result = json_decode($ret->getBody(), true);
        
        if ($result['status'] !== self::STATUS_OK) {
            throw new \Exception('Google Api Status : '. $result['status']);
        }
        
        return $result;
    }

    /**
     * Get lng lat by address
     *
     * @param string $address            
     * @throws \Exception
     *
     * @return array
     */
    public function getLngLat($address)
    {
        $this->params_location['address'] = urlencode($address);
        
        $conf = $this->getServiceLocator()->get('config');
        $conf_addr = $conf['address-conf'];
        $cli = new Client();
        $cli->setOptions($conf[$conf_addr['geoloc']['adapter']]);
        $cli->setUri($this->getUrlApiLocation());
        $ret = $cli->send();
        
        if ($ret->isClientError()) {
            throw new \Exception($ret->getReasonPhrase(), $ret->getStatusCode());
        }
        
        $result = json_decode($ret->getBody(), true);
        
        if ($result['status'] !== self::STATUS_OK) {
            throw new \Exception('Google Api Status : '. $result['status']);
        }
        
        $result['results'][0]['geometry']['location'];
    }

    /**
     * Get Timezone by address
     *
     * @param unknown $address            
     *
     * @return array
     */
    public function getTimezoneByAddr($address)
    {
        $geo = $this->getLngLat($address);
        
        return $this->getTimezone($geo[0], $geo[1]);
    }

    /**
     * Get Timezone
     * 
     * @param unknown $latitude            
     * @param unknown $longitude            
     * @throws \Exception
     *
     * @return array
     */
    public function getTimezone($latitude, $longitude)
    {
        $this->params_timezone['location'] = sprintf('%s,%s', $latitude, $longitude);
        $conf = $this->getServiceLocator()->get('config');
        $conf_addr = $conf['address-conf'];
        $cli = new Client();
        $cli->setOptions($conf[$conf_addr['geoloc']['adapter']]);
        $cli->setUri($this->getUrlApiTimezone());
        $ret = $cli->send();
        
        if ($ret->isClientError()) {
            throw new \Exception($ret->getReasonPhrase(), $ret->getStatusCode());
        }
        
        return json_decode($ret->getBody(), true);
    }

    /**
     * Convert array to string url
     *
     * @param unknown $params            
     * @return string
     */
    private function arrayToString($params)
    {
        $str_params = array();
        foreach ($params as $key => $value) {
            $str_params[] = $key . '=' . $value;
        }
        
        return implode("&", $str_params);
    }

    /**
     * Get url api google geocode
     *
     * @return string
     */
    private function getUrlApiLocation()
    {
        return $this->getServiceLocator()->get('config')['address-conf']['geoloc']['url'] . $this->api_location . '/' . $this->output_format . '?' . $this->arrayToString($this->params_location);
    }

    /**
     * Get url api google timezone
     *
     * @return string
     */
    private function getUrlApiTimezone()
    {
        return $this->getServiceLocator()->get('config')['address-conf']['geoloc']['url'] . $this->api_timezone . '/' . $this->output_format . '?' . $this->arrayToString($this->params_timezone);
    }

    /**
     * Set service locator
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
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
