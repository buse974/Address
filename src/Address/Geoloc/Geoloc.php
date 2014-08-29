<?php

namespace Address\Geoloc;

use Zend\Http\Client;

class Geoloc implements \Zend\ServiceManager\ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $api_location = 'geocode';
    protected $api_timezone = 'timezone';
    protected $params_location = array('sensor' => 'false', 'address' => null);
    protected $params_timezone = array('location' => null, 'timestamp' => 0);
    protected $output_format = 'json';
    
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
            throw new \Exception($ret->getReasonPhrase(),$ret->getStatusCode());
        }

        return json_decode($ret->getContent(), true);
    }
    
    public function getTimezoneByAddr($address)
    {
    	$geo = $this->getGeoloc($address);
    	
    	return $this->getTimezone($geo[0], $geo[1]);
    }
    
    public function getTimezone($latitude, $longitude)
    {
    	if(!is_double($latitude) && !is_double($longitude)){
    		return;
    	}
    	$this->params_timezone['location'] = $latitude . "," . $longitude;
    	$conf = $this->getServiceLocator()->get('config');
    	$conf_addr = $conf['address-conf'];
    	$cli = new Client();
    	$cli->setOptions($conf[$conf_addr['geoloc']['adapter']]);
    	$cli->setUri($this->getUrlApiTimezone());
    	$ret = $cli->send();
    	
    	if ($ret->isClientError()) {
    		throw new \Exception($ret->getReasonPhrase(),$ret->getStatusCode());
    	}
    	
    	return json_decode($ret->getContent(),true);
    }

    private function arrayToStringAddr($params)
    {
    	$str_params = "";
    	foreach ($params as $key => $value){
    		$str_params[] = $key . '=' . $value;
    	}
    	
    	return implode("&", $str_params);
    }
    
    private function getUrlApiLocation()
    {
    	return $this->getServiceLocator()->get('config')['address-conf']['geoloc']['url'] . $this->api_location . '/' . $this->output_format . '?' . $this->arrayToStringAddr($this->params_location);
    }
    
    private function getUrlApiTimezone()
    {
    	return $this->getServiceLocator()->get('config')['address-conf']['geoloc']['url'] . $this->api_timezone . '/' . $this->output_format . '?' . $this->arrayToStringAddr($this->params_timezone);
    }
    
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        if ($this->serviceLocator===null) {
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
