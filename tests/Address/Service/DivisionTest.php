<?php
namespace AddressTest\Service;

use Address\Service\Country;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class DivisionTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../config/application.config.php');
        
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
            ->setMethods(array('usePaginator','getList','count'))
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
        $serviceManager = $this->getApplication()->getServiceManager();
        
        $s_division = $this->getMockBuilder('Address\Service\Division')
            ->setMethods(array('getDivisionById'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $s_division->expects($this->once())
            ->method('getDivisionById')
            ->will($this->returnValue('obj_division'));
        
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_division', $s_division);
        
        $division = $serviceManager->get('addr_service_division');
        
        $this->assertEquals('obj_division', $division->getDivision(array('id' => 1)));
    }

    public function testGetDivisionByIdWhitoutArray()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        
        $s_division = $this->getMockBuilder('Address\Service\Division')
            ->setMethods(array('getDivisionById'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $s_division->expects($this->once())
            ->method('getDivisionById')
            ->will($this->returnValue('obj_division'));
        
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_division', $s_division);
        
        $division = $serviceManager->get('addr_service_division');
        
        $this->assertEquals('obj_division', $division->getDivision(1));
    }

    public function testGetDivisionByNameWihtoutArray()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        
        $s_division = $this->getMockBuilder('Address\Service\Division')
            ->setMethods(array('getDivisionByName'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $s_division->expects($this->once())
            ->method('getDivisionByName')
            ->will($this->returnValue('obj_division'));
        
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_division', $s_division);
        
        $division = $serviceManager->get('addr_service_division');
        
        $this->assertEquals('obj_division', $division->getDivision('name'));
    }

    public function testGetDivisionByArrayName()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        
        $s_division = $this->getMockBuilder('Address\Service\Division')
            ->setMethods(array('getDivisionByName'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $s_division->expects($this->once())
            ->method('getDivisionByName')
            ->will($this->returnValue('obj_division'));
        
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_division', $s_division);
        
        $division = $serviceManager->get('addr_service_division');
        
        $this->assertEquals('obj_division', $division->getDivision(array('name' => 'name')));
    }

    public function testGetDivisionById()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        
        $m_mapper = $this->getMockBuilder('Address\Mapper\Division')
            ->setMethods(array('select','current'))
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
        $serviceManager = $this->getApplication()->getServiceManager();
        
        // Mapper Division Mock
        $m_mapper = $this->getMockBuilder('\Address\Mapper\Division')
            ->setMethods(array('getDivisionByName','current','count'))
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
        $s_division = $this->getMockBuilder('\Address\Service\Division')
            ->setMethods(array('getServiceCountry','getCountry','getId','getMapper'))
            ->disableOriginalConstructor()
            ->getMock();
        
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
        
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('addr_service_division', $s_division);
        
        // TEST
        $division = $serviceManager->get('addr_service_division');
        $this->assertEquals('result', $division->getDivisionByName('france', 'country'));
    }

    public function testCanAddDivision()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        
        // Mock country
        $mockconutry = $this->getMock('\Object', ['getName','getId']);
        $mockconutry->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(12));
        
        $mockconutry->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('countryname'));
        
        $s_division = $this->getMockBuilder('\Address\Service\Division')
            ->setConstructorArgs([['prefix' => 'addr','name' => 'division']])
            ->setMethods(['getLngLat','getMapper','insert','getLastInsertValue','getServiceCountry','getCountry'])
            ->getMock();
        
        $s_division->expects($this->once())
            ->method('getLngLat')
            ->will($this->returnValue(['lng' => 2,'lat' => 3]));
        
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
        
        $s_division->setServiceLocator($serviceManager);
        
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
        
        $s_division = $this->getMockBuilder('\Address\Service\Division')
            ->setConstructorArgs([['prefix' => 'addr','name' => 'division']])
            ->setMethods(['getLngLat','getMapper','insert'])
            ->getMock();
        
        $s_division->expects($this->exactly(1))
            ->method('getMapper')
            ->will($this->returnSelf());
        
        $s_division->expects($this->once())
            ->method('getLngLat')
            ->will($this->returnValue(['lng' => 2,'lat' => 3]));
        
        $s_division->expects($this->once())
            ->method('insert')
            ->will($this->returnValue(0));
        
        $s_division->setServiceLocator($serviceManager);
        
        $s_division->add('division');
    }

    public function testGetLngLat()
    {
        // Mock country
        $mock = $this->getMock('geoloc', ['getLngLat']);
        $mock->expects($this->once())
            ->method('GetLngLat')
            ->with($this->equalTo('tata titi'))
            ->will($this->returnValue('ok'));
        
        $s_division = $this->getMockBuilder('\Address\Service\Division')
            ->setConstructorArgs([['prefix' => 'addr','name' => 'division']])
            ->setMethods(['getServiceLocator','get'])
            ->getMock();
        
        $s_division->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnSelf());
        
        $s_division->expects($this->exactly(1))
            ->method('get')
            ->will($this->returnValue($mock));

        $this->assertEquals('ok', $s_division->getLngLat('tata', 'titi'));
    }
}
