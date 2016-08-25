<?php

namespace AddressTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CountryTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__.'/../../config/application.config.php');

        parent::setUp();
    }

    public function testGetList()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $m_mapper = $this->getMockBuilder('Address\Mapper\Country')
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
        $serviceManager->setService('addr_mapper_country', $m_mapper);

        $country = $serviceManager->get('addr_service_country');

        $out = $country->getList();

        $this->assertArrayHasKey('count', $out);
        $this->assertArrayHasKey('results', $out);

        $this->assertEquals(6, $out['count']);
        $this->assertEquals('resultset', $out['results']);
    }

    public function testGetCountryByArrayId()
    {
        $s_country = $this->getMockServiceCountry(['getCountryById']);

        $s_country->expects($this->once())
            ->method('getCountryById')
            ->will($this->returnValue('obj_country'));

        $this->assertEquals('obj_country', $s_country->getCountry(array('id' => 1)));
    }

    public function testGetCountryByIdWhitoutArray()
    {
        $s_country = $this->getMockServiceCountry(['getCountryById']);

        $s_country->expects($this->once())
            ->method('getCountryById')
            ->will($this->returnValue('obj_country'));

        $this->assertEquals('obj_country', $s_country->getCountry(1));
    }

    public function testGetCountryByNameWihtoutArray()
    {
        $s_country = $this->getMockServiceCountry(['getCountryByName']);

        $s_country->expects($this->once())
            ->method('getCountryByName')
            ->will($this->returnValue('obj_country'));

        $this->assertEquals('obj_country', $s_country->getCountry('name'));
    }

    public function testGetCountryByArrayName()
    {
        $s_country = $this->getMockServiceCountry(['getCountryByName']);

        $s_country->expects($this->once())
            ->method('getCountryByName')
            ->will($this->returnValue('obj_country'));

        $this->assertEquals('obj_country', $s_country->getCountry(array('name' => 'name')));
    }

    public function testGetCountryById()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $m_mapper = $this->getMockBuilder('Address\Mapper\Country')
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
        $serviceManager->setService('addr_mapper_country', $m_mapper);

        $country = $serviceManager->get('addr_service_country');

        $this->assertEquals('result', $country->getCountryById(1));
    }

    public function testGetCountryByName()
    {
        $m_mapper = $this->getMockBuilder('\Address\Mapper\Country')
            ->setMethods(array('getCountryByName', 'current', 'count'))
            ->disableOriginalConstructor()
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('getCountryByName')
            ->will($this->returnSelf());

        $m_mapper->expects($this->once())
            ->method('current')
            ->will($this->returnValue('result'));

        $m_mapper->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $s_country = $this->getMockServiceCountry(['getMapper']);

        $s_country->expects($this->once())
            ->method('getMapper')
            ->will($this->returnValue($m_mapper));

        $s_country->setContainer($this->getApplicationServiceLocator());

        $this->assertEquals('result', $s_country->getCountryByName('france'));
    }

    public function testGetCountryByNameGeoloc()
    {
        $m_mapper = $this->getMockBuilder('\Address\Mapper\Country')
            ->setMethods(array('getCountryByName', 'insert', 'count', 'getLastInsertValue'))
            ->disableOriginalConstructor()
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('getCountryByName')
            ->will($this->returnSelf());

        $m_mapper->expects($this->once())
            ->method('insert')
            ->will($this->returnValue(1));

        $m_mapper->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $m_mapper->expects($this->once())
            ->method('getLastInsertValue')
            ->will($this->returnValue(5));

        $s_country = $this->getMockServiceCountry(['getLngLat', 'getMapper']);

        $s_country->expects($this->once())
            ->method('getLngLat')
            ->will($this->returnValue(['lng' => 1, 'lat' => 2]));

        $s_country->expects($this->any())
            ->method('getMapper')
            ->will($this->returnValue($m_mapper));

        $s_country->setContainer($this->getApplicationServiceLocator());

        $this->assertInstanceOf('\Address\Model\base\Country', $s_country->getCountryByName('france'));
    }

    public function testExceptionCountryByName()
    {
        $this->setExpectedException('Exception', 'Error: insert country', 0);

        $m_mapper = $this->getMockBuilder('\Address\Mapper\Country')
            ->setMethods(array('getCountryByName', 'insert', 'count'))
            ->disableOriginalConstructor()
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('getCountryByName')
            ->will($this->returnSelf());
        $m_mapper->expects($this->once())
            ->method('insert')
            ->will($this->returnValue(0));
        $m_mapper->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $s_country = $this->getMockServiceCountry(['getLngLat', 'getMapper']);

        $s_country->expects($this->once())
            ->method('getLngLat')
            ->will($this->returnValue(['lng' => 1, 'lat' => 2]));

        $s_country->expects($this->any())
            ->method('getMapper')
            ->will($this->returnValue($m_mapper));

        $s_country->setContainer($this->getApplicationServiceLocator());

        $s_country->getCountryByName('france');
    }

    public function testGetLngLat()
    {
        // Mock country
        $mock = $this->getMockBuilder('Address\Geoloc\Geoloc')
            ->setMethods(['getLngLat'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $mock->expects($this->once())
            ->method('GetLngLat')
            ->with($this->equalTo('tata'))
            ->will($this->returnValue('ok'));

        $s_division = $this->getMockServiceCountry(['get']);
        $s_division->setContainer($s_division);
        
        $s_division->expects($this->exactly(1))
            ->method('get')
            ->will($this->returnValue($mock));

        $this->assertEquals('ok', $s_division->getLngLat('tata'));
    }

    public function getMockServiceCountry(array $methods = [])
    {
        $s_country = $this->getMockBuilder('\Address\Service\Country')
            ->setConstructorArgs([['prefix' => 'addr', 'name' => 'country']])
            ->setMethods($methods)
            ->getMock();

        $s_country->setContainer($this->getApplicationServiceLocator());

        return $s_country;
    }
}
