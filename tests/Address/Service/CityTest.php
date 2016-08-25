<?php

namespace AddressTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CityTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__.'/../../config/application.config.php');

        parent::setUp();
    }

    public function testGetList()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $m_mapper = $this->getMockBuilder('\Address\Mapper\City')
            ->setMethods(array('usePaginator', 'getList', 'count'))
            ->disableOriginalConstructor()
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('usePaginator')
            ->with(array())
            ->will($this->returnSelf());

        $m_mapper->expects($this->once())
            ->method('getList')
            ->with(array())
            ->will($this->returnValue('resultset'));

        $m_mapper->expects($this->once())
            ->method('count')
            ->will($this->returnValue(6));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_mapper_city', $m_mapper);

        $division = $serviceManager->get('addr_service_city');

        $out = $division->getList();

        $this->assertArrayHasKey('count', $out);
        $this->assertArrayHasKey('results', $out);

        $this->assertEquals(6, $out['count']);
        $this->assertEquals('resultset', $out['results']);
    }

    public function testGetCityByArrayId()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setMethods(array('getCityById'))
            ->disableOriginalConstructor()
            ->getMock();

        $s_city->expects($this->once())
            ->method('getCityById')
            ->will($this->returnValue('obj_city'));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_city', $s_city);

        $division = $serviceManager->get('addr_service_city');

        $this->assertEquals('obj_city', $division->getCity(array('id' => 1)));
    }

    public function testGetCityByIdWhitoutArray()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setMethods(array('getCityById'))
            ->disableOriginalConstructor()
            ->getMock();

        $s_city->expects($this->once())
            ->method('getCityById')
            ->will($this->returnValue('obj_city'));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_city', $s_city);

        $division = $serviceManager->get('addr_service_city');

        $this->assertEquals('obj_city', $division->getCity(1));
    }

    public function testGetCityByNameWihtoutArray()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setMethods(array('getCityByName'))
            ->disableOriginalConstructor()
            ->getMock();

        $s_city->expects($this->once())
            ->method('getCityByName')
            ->will($this->returnValue('obj_city'));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_city', $s_city);

        $division = $serviceManager->get('addr_service_city');

        $this->assertEquals('obj_city', $division->getCity('name'));
    }

    public function testGetCityByArrayName()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setMethods(array('getCityByName'))
            ->disableOriginalConstructor()
            ->getMock();

        $s_city->expects($this->once())
            ->method('getCityByName')
            ->will($this->returnValue('obj_city'));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_city', $s_city);

        $division = $serviceManager->get('addr_service_city');

        $this->assertEquals('obj_city', $division->getCity(array('name' => 'name')));
    }

    public function testGetCityById()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $m_mapper = $this->getMockBuilder('\Address\Service\City')
            ->setMethods(array('select', 'current'))
            ->disableOriginalConstructor()
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('select')
            ->will($this->returnSelf());

        $m_mapper->expects($this->once())
            ->method('current')
            ->will($this->returnValue('result'));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_mapper_city', $m_mapper);

        $division = $serviceManager->get('addr_service_city');

        $this->assertEquals('result', $division->getCityById(1));
    }

    public function testGetCityByName()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        // Mapper Division Mock
        $m_mapper = $this->getMockBuilder('\Address\Service\City')
            ->setMethods(array('getCityByName', 'current', 'count'))
            ->disableOriginalConstructor()
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('getCityByName')
            ->will($this->returnSelf());

        $m_mapper->expects($this->once())
            ->method('current')
            ->will($this->returnValue('result'));

        $m_mapper->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        // Service Division Mock
        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setMethods(array('getServiceCountry', 'getServiceDivision', 'getCountry', 'getDivision', 'getId', 'getMapper'))
            ->disableOriginalConstructor()
            ->getMock();

        $m_division = $this->getMockBuilder('Address\Service\Division')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_division->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));

        $m_country = $this->getMockBuilder('Address\Service\Country')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_country->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(4));

        $s_city->expects($this->once())
            ->method('getServiceCountry')
            ->will($this->returnSelf());

        $s_city->expects($this->once())
            ->method('getServiceDivision')
            ->will($this->returnSelf());

        $s_city->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue($m_country));

        $s_city->expects($this->once())
            ->method('getDivision')
            ->will($this->returnValue($m_division));

        $s_city->expects($this->once())
            ->method('getMapper')
            ->will($this->returnValue($m_mapper));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_city', $s_city);

        // TEST
        $division = $serviceManager->get('addr_service_city');
        $this->assertEquals('result', $division->getCityByName('nice', 'country', 'division'));
    }

    public function testCanAddCity()
    {
        $container = $this->getApplicationServiceLocator();

        // Mock country
        $mockconutry = $this->getMockBuilder('stdClass')
            ->setMethods(['getName', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $mockconutry->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(12));

        $mockconutry->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('countryname'));

        // Mock division
        $mockdivision = $this->getMockBuilder('stdClass')
            ->setMethods(['getName', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockdivision->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(12));

        $mockdivision->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('divisionname'));

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setConstructorArgs([['prefix' => 'addr', 'name' => 'city']])
            ->setMethods(['getLngLat', 'getMapper', 'insert', 'getLastInsertValue', 'getServiceCountry', 'getCountry', 'getServiceDivision', 'getDivision'])
            ->getMock();

        $s_city->expects($this->once())
            ->method('getLngLat')
            ->will($this->returnValue(['lng' => 2, 'lat' => 3]));

        $s_city->expects($this->exactly(2))
            ->method('getMapper')
            ->will($this->returnSelf());

        $s_city->expects($this->once())
            ->method('getLastInsertValue')
            ->will($this->returnValue(5));

        $s_city->expects($this->once())
            ->method('getServiceCountry')
            ->will($this->returnSelf());

        $s_city->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue($mockconutry));

        $s_city->expects($this->once())
            ->method('getServiceDivision')
            ->will($this->returnSelf());

        $s_city->expects($this->once())
            ->method('getDivision')
            ->will($this->returnValue($mockdivision));

        $s_city->expects($this->once())
            ->method('insert')
            ->will($this->returnValue(1));

        $s_city->setContainer($container);
            
        
        $m_city = $s_city->add('city', 'division', 'country', 'short', '13000');

        $this->assertInstanceOf('\Address\Model\base\City', $m_city);
        $this->assertEquals($m_city->getId(), 5);
        $this->assertEquals($m_city->getName(), 'city');
        $this->assertEquals($m_city->getLibelle(), 'short');
        $this->assertEquals($m_city->getCode(), 13000);
        $this->assertEquals($m_city->getDivisionId(), 12);
        $this->assertEquals($m_city->getCountryId(), 12);
        $this->assertEquals($m_city->getLatitude(), 3);
        $this->assertEquals($m_city->getLongitude(), 2);
    }

    public function testCanUpdateCity()
    {
        $container = $this->getApplicationServiceLocator();
        
        $data = ['city' => ['id' => 1,'name' => 'marseille','code' => 13000,'libelle' => 'libelle'],'country' => [],'division' => []];

        //Mapper
        $mockmapper = $this->getMockBuilder('stdClass')
            ->setMethods(['update'])
            ->getMock();
        $mockmapper->expects($this->once())
            ->method('update')
            ->will($this->returnValue(1));

        //Mock country
        $mockconutry = $this->getMockBuilder('stdClass')
            ->setMethods(['getId'])
            ->getMock();
        $mockconutry->expects($this->once())
            ->method('getId');

        //Mock division
        $mockdivision = $this->getMockBuilder('stdClass')
            ->setMethods(['getId'])
            ->getMock();
        $mockdivision->expects($this->once())
            ->method('getId');

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setConstructorArgs([['prefix' => 'addr', 'name' => 'city']])
            ->setMethods(['getMapper', 'getServiceCountry', 'getCountry', 'getServiceDivision', 'getDivision'])
            ->getMock();

        $s_city->expects($this->once())
            ->method('getMapper')
            ->will($this->returnValue($mockmapper));

        $s_city->expects($this->once())
            ->method('getServiceCountry')
            ->will($this->returnSelf());

        $s_city->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue($mockconutry));

        $s_city->expects($this->once())
            ->method('getServiceDivision')
            ->will($this->returnSelf());

        $s_city->expects($this->once())
            ->method('getDivision')
            ->will($this->returnValue($mockdivision));

        $s_city->setContainer($container);

        $this->assertEquals(1, $s_city->update($data));
    }

    public function testCanDeleteCity()
    {
        // Mapper
        $mockmapper = $this->getMockBuilder('stdClass')
            ->setMethods(['delete'])
            ->getMock();
        
        $mockmapper->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(1));

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setConstructorArgs([['prefix' => 'addr', 'name' => 'city']])
            ->setMethods(['getMapper'])
            ->getMock();

        $s_city->expects($this->once())
            ->method('getMapper')
            ->will($this->returnValue($mockmapper));

        $s_city->setContainer($this->getApplicationServiceLocator());

        $this->assertEquals(1, $s_city->delete(1));
    }

    public function testCanUpdateCityNull()
    {
        $data = ['city' => ['name' => 'marseille','code' => 13000,'libelle' => 'libelle'],'country' => [],'division' => []];

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setConstructorArgs([['prefix' => 'addr', 'name' => 'city']])
            ->setMethods(['getMapper'])
            ->getMock();

        $this->assertEquals(null, $s_city->update($data));
    }

    public function testCanAddCityException()
    {
        $this->setExpectedException('Exception', 'Error: insert city', 0);

        $container = $this->getApplicationServiceLocator();

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setConstructorArgs([['prefix' => 'addr', 'name' => 'city']])
            ->setMethods(['getLngLat', 'getMapper', 'insert'])
            ->getMock();

        $s_city->expects($this->exactly(1))
            ->method('getMapper')
            ->will($this->returnSelf());

        $s_city->expects($this->once())
            ->method('getLngLat')
            ->will($this->returnValue(['lng' => 2, 'lat' => 3]));

        $s_city->expects($this->once())
            ->method('insert')
            ->will($this->returnValue(0));

        $s_city->setContainer($container);

        $s_city->add('city');
    }

    public function testGetLngLat()
    {
        // Mock country
        $mock = $this->getMockBuilder('Address\Geoloc\Geoloc')
            ->setMethods(['getLngLat', 'get'])
            ->setConstructorArgs([[],[]])
            ->getMock();
        
        $mock->expects($this->any())
            ->method('GetLngLat')
            ->with($this->equalTo('tata titi tutu'))
            ->will($this->returnValue('ok'));
        
        $mock->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $s_city = $this->getMockBuilder('\Address\Service\City')
            ->setConstructorArgs([['prefix' => 'addr', 'name' => 'division']])
            ->setMethods(['getServiceLocator', 'get'])
            ->getMock();
        
        $class = new \ReflectionClass('\Address\Service\City');
        $property = $class->getProperty("container");
        $property->setAccessible(true);
        $property->setValue($s_city, $mock);
            
        $this->assertEquals('ok', $s_city->getLngLat('tata', 'titi', 'tutu'));
    }

    public function testGetServiceCountry()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_country', 'addr_service_country');

        $city = $serviceManager->get('addr_service_city');

        $this->assertEquals('addr_service_country', $city->getServiceCountry());
    }

    public function testGetServiceDivision()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_division', 'addr_service_division');

        $city = $serviceManager->get('addr_service_city');

        $this->assertEquals('addr_service_division', $city->getServiceDivision());
    }
}
