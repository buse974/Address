<?php

namespace AddressTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class DivisionTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__.'/../../config/application.config.php');

        parent::setUp();
    }

    public function testGetServiceCountry()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_country', 'addr_service_country');

        $division = $serviceManager->get('addr_service_division');

        $this->assertEquals('addr_service_country', $division->getServiceCountry());
    }

    public function testGetList()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $m_mapper = $this->getMockBuilder('Address\Mapper\Division')
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
        $serviceManager->setService('addr_mapper_division', $m_mapper);

        $division = $serviceManager->get('addr_service_division');

        $out = $division->getList();

        $this->assertArrayHasKey('count', $out);
        $this->assertArrayHasKey('results', $out);

        $this->assertEquals(6, $out['count']);
        $this->assertEquals('resultset', $out['results']);
    }

    public function testGetDivisionByArrayId()
    {
        $s_division = $this->getMockServiceDivision(['getDivisionById']);

        $s_division->expects($this->once())
            ->method('getDivisionById')
            ->will($this->returnValue('obj_division'));

        $this->assertEquals('obj_division', $s_division->getDivision(array('id' => 1)));
    }

    public function testGetDivisionByIdWhitoutArray()
    {
        $s_division = $this->getMockServiceDivision(['getDivisionById']);

        $s_division->expects($this->once())
            ->method('getDivisionById')
            ->will($this->returnValue('obj_division'));

        $this->assertEquals('obj_division', $s_division->getDivision(1));
    }

    public function testGetDivisionByNameWihtoutArray()
    {
        $s_division = $this->getMockServiceDivision(['getDivisionByName']);

        $s_division->expects($this->once())
            ->method('getDivisionByName')
            ->will($this->returnValue('obj_division'));

        $this->assertEquals('obj_division', $s_division->getDivision('name'));
    }

    public function testGetDivisionByArrayName()
    {
        $s_division = $this->getMockServiceDivision(['getDivisionByName']);

        $s_division->expects($this->once())
            ->method('getDivisionByName')
            ->will($this->returnValue('obj_division'));

        $this->assertEquals('obj_division', $s_division->getDivision(array('name' => 'name')));
    }

    public function testGetDivisionById()
    {
        $serviceManager = $this->getApplication()->getServiceManager();

        $m_mapper = $this->getMockBuilder('Address\Mapper\Division')
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
        $serviceManager->setService('addr_mapper_division', $m_mapper);

        $division = $serviceManager->get('addr_service_division');

        $this->assertEquals('result', $division->getDivisionById(1));
    }

    public function testGetDivisionByName()
    {
        // Mapper Division Mock
        $m_mapper = $this->getMockBuilder('\Address\Mapper\Division')
            ->setMethods(array('getDivisionByName', 'current', 'count'))
            ->disableOriginalConstructor()
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('getDivisionByName')
            ->will($this->returnSelf());

        $m_mapper->expects($this->once())
            ->method('current')
            ->will($this->returnValue('result'));

        $m_mapper->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        // Service Division Mock
        $s_division = $this->getMockServiceDivision(['getServiceCountry', 'getCountry', 'getId', 'getMapper']);

        $s_division->expects($this->once())
            ->method('getServiceCountry')
            ->will($this->returnSelf());

        $s_division->expects($this->once())
            ->method('getCountry')
            ->will($this->returnSelf());

        $s_division->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));

        $s_division->expects($this->once())
            ->method('getMapper')
            ->will($this->returnValue($m_mapper));

        $this->assertEquals('result', $s_division->getDivisionByName('france', 'country'));
    }

    public function testCanAddDivision()
    {
        // Mock country
        $mockconutry = $this->getMockBuilder('stdClass')
            ->setMethods(['getName', 'getId'])
            ->getMock();
        
        $mockconutry->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(12));

        $mockconutry->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('countryname'));

        $s_division = $this->getMockServiceDivision(['getLngLat', 'getMapper', 'insert', 'getLastInsertValue', 'getServiceCountry', 'getCountry']);

        $s_division->expects($this->once())
            ->method('getLngLat')
            ->will($this->returnValue(['lng' => 2, 'lat' => 3]));

        $s_division->expects($this->exactly(2))
            ->method('getMapper')
            ->will($this->returnSelf());

        $s_division->expects($this->once())
            ->method('getLastInsertValue')
            ->will($this->returnValue(5));

        $s_division->expects($this->once())
            ->method('getServiceCountry')
            ->will($this->returnSelf());

        $s_division->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue($mockconutry));

        $s_division->expects($this->once())
            ->method('insert')
            ->will($this->returnValue(1));

        $m_division = $s_division->add('division', 'country', 'short', '13000');

        $this->assertInstanceOf('\Address\Model\base\Division', $m_division);
        $this->assertEquals($m_division->getId(), 5);
        $this->assertEquals($m_division->getName(), 'division');
        $this->assertEquals($m_division->getLibelle(), '13000');
        $this->assertEquals($m_division->getShortName(), 'short');
        $this->assertEquals($m_division->getCountryId(), 12);
        $this->assertEquals($m_division->getLatitude(), 3);
        $this->assertEquals($m_division->getLongitude(), 2);
    }

    public function testCanAddDivisionException()
    {
        $this->setExpectedException('Exception', 'Error: insert division', 0);

        $serviceManager = $this->getApplication()->getServiceManager();

        $s_division = $this->getMockServiceDivision(['getLngLat', 'getMapper', 'insert']);

        $s_division->expects($this->exactly(1))
            ->method('getMapper')
            ->will($this->returnSelf());

        $s_division->expects($this->once())
            ->method('getLngLat')
            ->will($this->returnValue(['lng' => 2, 'lat' => 3]));

        $s_division->expects($this->once())
            ->method('insert')
            ->will($this->returnValue(0));

        $s_division->add('division');
    }

    public function testGetLngLat()
    {
        // Mock country
        $mock = $this->getMockBuilder('Address\Geoloc\Geoloc')
            ->setMethods(['getLngLat'])
            ->setConstructorArgs([[],[]])
            ->getMock();
        
        $mock->expects($this->once())
            ->method('GetLngLat')
            ->with($this->equalTo('tata titi'))
            ->will($this->returnValue('ok'));

        $s_division = $this->getMockServiceDivision(['get']);
        $s_division->setContainer($s_division);
        
        $s_division->expects($this->exactly(1))
            ->method('get')
            ->will($this->returnValue($mock));

        $this->assertEquals('ok', $s_division->getLngLat('tata', 'titi'));
    }

    public function getMockServiceDivision(array $methods = [])
    {
        $s_address = $this->getMockBuilder('\Address\Service\Division')
            ->setConstructorArgs([['prefix' => 'addr', 'name' => 'division']])
            ->setMethods($methods)
            ->getMock();

        $s_address->setContainer($this->getApplicationServiceLocator());

        return $s_address;
    }
}
