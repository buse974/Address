<?php
namespace AddressTest\Geoloc;

use Zend\Http\Client;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class GeolocTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../config/application.config.php');
        
        parent::setUp();
    }

    public function testGetTimezone()
    {
        $m_geoloc = $this->getMockServiceGeoloc(['send']);
        $m_geoloc->expects($this->any())
            ->method('send')
            ->will($this->returnValue(['status' => 'OK','timeZoneId' => 'Europe/Paris']));
        
        $out = $m_geoloc->getTimezone(46.227638, 2.213749);
        
        $this->assertArrayHasKey('timeZoneId', $out);
        $this->assertArrayHasKey('status', $out);
        $this->assertEquals('OK', $out['status']);
        $this->assertEquals('Europe/Paris', $out['timeZoneId']);
    }

    public function testGetTimezoneNull()
    {
        $m_geoloc = $this->getMockServiceGeoloc(['send']);
        $m_geoloc->expects($this->any())
            ->method('send')
            ->will($this->returnValue(['status' => 'NULL','timeZoneId' => 'Europe/Paris']));
        
        $out = $m_geoloc->getTimezone(46.227638, 2.213749);
        
        $this->assertNull($out);
    }

    public function testGetLngLat()
    {
        $data = ['status' => 'OK','results' => [0 => ['geometry' => ['location' => ['lat' => 1,'lng' => 2]]]]];
        
        $m_geoloc = $this->getMockServiceGeoloc(['send']);
        $m_geoloc->expects($this->any())
            ->method('send')
            ->will($this->returnValue($data));
        
        $out = $m_geoloc->getLngLat("uneaddress");
        
        $this->assertArrayHasKey('lat', $out);
        $this->assertArrayHasKey('lng', $out);
        $this->assertEquals(1, $out['lat']);
        $this->assertEquals(2, $out['lng']);
    }

    public function testGetLngLatNull()
    {
        $data = ['status' => 'NULL','results' => [0 => ['geometry' => ['location' => ['lat' => 1,'lng' => 2]]]]];
        
        $m_geoloc = $this->getMockServiceGeoloc(['send']);
        $m_geoloc->expects($this->any())
            ->method('send')
            ->will($this->returnValue($data));
        
        $out = $m_geoloc->getLngLat("uneaddress");
        
        $this->assertNull($out);
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
        
        $this->assertEquals('https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=address', $reflection->invoke($geoloc, "address"));
    }

    public function testGetUrlApiTimezone()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        $geoloc = $serviceManager->get('geoloc');
        
        $reflectionClass = new \ReflectionClass('Address\Geoloc\Geoloc');
        $reflection = $reflectionClass->getMethod('getUrlApiTimezone');
        $reflection->setAccessible(true);
        
        $this->assertEquals('https://maps.googleapis.com/maps/api/timezone/json?location=1,2&timestamp=0', $reflection->invoke($geoloc, 1, 2));
    }

    public function testSend()
    {
        $ar = ['adapter' => [],'address-conf' => ['geoloc' => ['adapter' => 'adapter']]];
        
        $geoloc = $this->getMockServiceGeoloc(['getServiceLocator','get','getClient']);
        $client = $this->getMock('client', ['setOptions','setUri','send','isClientError','getBody']);
        
        $geoloc->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnSelf());
        $geoloc->expects($this->any())
            ->method('get')
            ->will($this->returnValue($ar));
        $geoloc->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($client));
        
        $client->expects($this->any())
            ->method('send')
            ->will($this->returnSelf());
        
        $client->expects($this->any())
            ->method('isClientError')
            ->will($this->returnValue(1));
        $client->expects($this->any())
        ->method('getBody')
        ->will($this->returnValue('{}'));
        
        $this->assertNull($geoloc->send('http://google.fr'));
    }
    
    public function testSendNULL()
    {
        $ar = ['adapter' => [],'address-conf' => ['geoloc' => ['adapter' => 'adapter']]];
    
        $geoloc = $this->getMockServiceGeoloc(['getServiceLocator','get','getClient']);
        $client = $this->getMock('client', ['setOptions','setUri','send','isClientError','getBody']);
    
        $geoloc->expects($this->any())
        ->method('getServiceLocator')
        ->will($this->returnSelf());
        $geoloc->expects($this->any())
        ->method('get')
        ->will($this->returnValue($ar));
        $geoloc->expects($this->any())
        ->method('getClient')
        ->will($this->returnValue($client));
    
        $client->expects($this->any())
        ->method('send')
        ->will($this->returnSelf());
    
        $client->expects($this->any())
        ->method('isClientError')
        ->will($this->returnValue(null));
        $client->expects($this->any())
        ->method('getBody')
        ->will($this->returnValue('{}'));
    
        $this->assertEquals($geoloc->send('http://google.fr'), []);
    }

    public function getMockServiceGeoloc(array $methods = [])
    {
        $geoloc = $this->getMockBuilder('\Address\Geoloc\Geoloc')
            ->setMethods($methods)
            ->getMock();
        
        $geoloc->setServiceLocator($this->getApplication()
            ->getServiceManager());
        
        return $geoloc;
    }
}
