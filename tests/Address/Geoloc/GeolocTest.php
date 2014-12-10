<?php

namespace AddressTest\Geoloc;

use Zend\Http\Client;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class GeolocTest extends AbstractHttpControllerTestCase
{
	public function setUp()
	{
		$this->setApplicationConfig(
				include __DIR__ . '/../../config/application.config.php'
		);
	
		parent::setUp();
	}
	
    public function testGetGeoloc()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	$geoloc = $serviceManager->get('geoloc');
    	
    	$out = $geoloc->getGeoloc('france');
    	
    	$this->assertArrayHasKey('results', $out);
    	$this->assertArrayHasKey('status', $out);
    	
    	$this->assertEquals('OK', $out['status']);
    }
    
    public function testGetTimezone()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	$geoloc = $serviceManager->get('geoloc');

    	$out = $geoloc->getTimezone(46.227638,2.213749);

    	$this->assertArrayHasKey('timeZoneId', $out);
    	$this->assertArrayHasKey('status', $out); 
    	$this->assertEquals('OK', $out['status']);
    	$this->assertEquals('Europe/Paris', $out['timeZoneId']);
    }

    public function testArrayToString()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	$geoloc = $serviceManager->get('geoloc');
    	 
    	$reflectionClass = new \ReflectionClass('Address\Geoloc\Geoloc');
    	$reflection = $reflectionClass->getMethod('arrayToString');
    	$reflection->setAccessible(true);
    	
    	$this->assertEquals('key=value&key2=value2', $reflection->invokeArgs($geoloc, array(array('key' => 'value','key2' => 'value2'))));
    }
    
    public function testGetUrlApiLocation()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	$geoloc = $serviceManager->get('geoloc');
    	
    	$reflectionClass = new \ReflectionClass('Address\Geoloc\Geoloc');
        $reflection = $reflectionClass->getMethod('getUrlApiLocation');
        $reflection->setAccessible(true);
        
    	$this->assertEquals('https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=', $reflection->invoke($geoloc));
    }
    
    public function testGetUrlApiTimezone()
    {
    	$serviceManager = $this->getApplication()->getServiceManager();
    	$geoloc = $serviceManager->get('geoloc');
    	 
    	$reflectionClass = new \ReflectionClass('Address\Geoloc\Geoloc');
    	$reflection = $reflectionClass->getMethod('getUrlApiTimezone');
    	$reflection->setAccessible(true);
    	
    	$this->assertEquals('https://maps.googleapis.com/maps/api/timezone/json?location=&timestamp=0', $reflection->invoke($geoloc));
    }
    
    public function testGetTimezoneByAddr()
    {
    	$m_geoloc = $this->getMockBuilder('Address\Geoloc\Geoloc')
    					 ->setMethods(array('getLngLat','getTimezone'))
    	                 ->disableOriginalConstructor()
    	                 ->getMock();
    	
    	$m_geoloc->expects($this->once())
    	         ->method('getLngLat')
    			 ->will($this->returnValue('toto'));
    	
    	$m_geoloc->expects($this->once())
    	         ->method('getTimezone')
    	         ->will($this->returnValue('timezonebyaddr'));
    	         
    	$geo = $m_geoloc->getTimezoneByAddr('france');
    }
}
