<?php

namespace AddressTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AddressTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__.'/../../config/application.config.php');

        parent::setUp();
    }

    public function testGetAddressByArrayId()
    {
        $s_address = $this->getMockServiceAddress(['getAddressById']);

        $s_address->expects($this->once())
            ->method('getAddressById')
            ->will($this->returnValue('obj_address'));

        $this->assertEquals('obj_address', $s_address->getAddress(array('id' => 1)));
    }

    public function testGetAddressByIdWhitoutArray()
    {
        $s_address = $this->getMockServiceAddress(['getAddressById']);

        $s_address->expects($this->once())
            ->method('getAddressById')
            ->will($this->returnValue('obj_address'));

        $this->assertEquals('obj_address', $s_address->getAddress(1));
    }

    public function testGetAddressByArrayName()
    {
        $s_address = $this->getMockServiceAddress(['getAddressByArray']);

        $s_address->expects($this->once())
            ->method('getAddressByArray')
            ->will($this->returnValue('obj_address'));

        $this->assertEquals('obj_address', $s_address->getAddress(array('name' => 'name')));
    }

    public function testGetAddressById()
    {
        $model = $this->getMockBuilder('stdClass')
            ->setMethods(array('getLatitude', 'getLongitude'))
            ->getMock();

        $m_mapper = $this->getMockBuilder('stdClass')
            ->setMethods(array('get', 'current'))
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('get')
            ->will($this->returnSelf());

        $m_mapper->expects($this->once())
            ->method('current')
            ->will($this->returnValue($model));

        $s_address = $this->getMockServiceAddress(['select', 'current', 'getMapper', 'updateLngLatTmz']);

        $s_address->expects($this->any())
            ->method('getMapper')
            ->will($this->returnValue($m_mapper));

        $s_address->getAddressById(1);
    }

    public function testCanAddAddress()
    {
        $data = ['street_name' => 'street_name','street_no' => 'street_no','street_type' => 'street_type','floor' => 'floor','door' => 'door','apartment' => 'apartment','building' => 'building','longitude' => 88,'latitude' => 77,'city' => [],'country' => [],'division' => []];

        // Mapper
        $m_mapper = $this->getMockBuilder('stdClass')
            ->setMethods(array('selectByArray', 'current', 'count', 'insert', 'getLastInsertValue'))
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('selectByArray')
            ->will($this->returnSelf());

        $m_mapper->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        // Mock city
        $mockcity = $this->getMockBuilder('Address\Service\City')->setMethods(['getId', 'getName'])->getMock();
        $mockcity->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $mockconutry = $this->getMockBuilder('Address\Service\Country')->setMethods(['getId', 'getName'])->getMock();  
        $mockconutry->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(3));
        $mockdivision = $this->getMockBuilder('Address\Service\Division')->setMethods(['getId', 'getName'])->getMock();  
        $mockdivision->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));

        $s_address = $this->getMockServiceAddress(['getServiceGeoloc', 'getTimezone', 'getMapper', 'getServiceCity', 'getCity', 'getServiceCountry', 'getCountry', 'getServiceDivision', 'getDivision']);

        $s_address->expects($this->any())
            ->method('getTimezone')
            ->will($this->returnValue(['timeZoneId' => 'timeZoneId']));

        $s_address->expects($this->any())
            ->method('getServiceGeoloc')
            ->will($this->returnSelf());

        $s_address->expects($this->any())
            ->method('getMapper')
            ->will($this->returnValue($m_mapper));

        $s_address->expects($this->any())
            ->method('getServiceCountry')
            ->will($this->returnSelf());

        $s_address->expects($this->any())
            ->method('getCountry')
            ->will($this->returnValue($mockconutry));

        $s_address->expects($this->any())
            ->method('getServiceDivision')
            ->will($this->returnSelf());

        $s_address->expects($this->any())
            ->method('getDivision')
            ->will($this->returnValue($mockdivision));

        $s_address->expects($this->any())
            ->method('getServiceCity')
            ->will($this->returnSelf());

        $s_address->expects($this->any())
            ->method('getCity')
            ->will($this->returnValue($mockcity));

        $f = $s_address->getAddressByArray($data)->toArray();

        $data['city_id'] = 1;
        $data['division_id'] = 2;
        $data['country_id'] = 3;
        $data['timezone'] = 'timeZoneId';
        unset($data['city']);
        unset($data['division']);
        unset($data['country']);

        $this->assertEquals($data, $f);
    }

    public function testCanAddAddressException()
    {
        $this->setExpectedException('Exception', 'Error: insert city', 0);

        $s_address = $this->getMockServiceAddress(['getMapper', 'getLngLat', 'insert']);

        $s_address->expects($this->any())
            ->method('getMapper')
            ->will($this->returnSelf());

        $s_address->expects($this->any())
            ->method('insert')
            ->will($this->returnValue(0));

        $s_address->add('street_no', 'street_type', 'street_name', 'floor', 'door', 'apartment', 'building');
    }

    public function testCanGetAddress()
    {
        $data = ['street_name' => 'street_name','street_no' => 'street_no','street_type' => 'street_type','floor' => 'floor','door' => 'door','apartment' => 'apartment','building' => 'building'];

        // Mapper
        $m_mapper = $this->getMockBuilder('\Address\Mapper\Address')
            ->setMethods(array('selectByArray', 'current', 'count', 'insert', 'getLastInsertValue'))
            ->disableOriginalConstructor()
            ->getMock();

        $m_mapper->expects($this->once())
            ->method('selectByArray')
            ->will($this->returnSelf());

        $m_mapper->expects($this->any())
            ->method('current')
            ->will($this->returnValue('result'));

        $m_mapper->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $s_address = $this->getMockServiceAddress(['getMapper', 'getServiceCity', 'getCity', 'getServiceCountry', 'getCountry', 'getServiceDivision', 'getDivision']);

        $s_address->expects($this->any())
            ->method('getMapper')
            ->will($this->returnValue($m_mapper));

        $this->assertEquals('result', $s_address->getAddressByArray($data));
    }

    public function testGetLngLat()
    {
        // Mock country
        $mock = $this->getMockBuilder('Address\Geoloc\Geoloc')
            ->setMethods(['getLngLat'])
            ->setConstructorArgs([[],[]])->getMock();

        $mock->expects($this->once())
            ->method('GetLngLat')
            ->with($this->equalTo('num type name city country division'))
            ->will($this->returnValue('ok'));

        $s_address = $this->getMockServiceAddress(['getServiceGeoloc']);
        $s_address->expects($this->exactly(1))
            ->method('getServiceGeoloc')
            ->will($this->returnValue($mock));

        $this->assertEquals('ok', $s_address->getLngLat('num', 'type', 'name', 'city', 'country', 'division'));
    }

    public function testCanInitLngLat()
    {
        $m_address = $this->getMockServiceAddress(['getId']);
        
        $m_address->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $s_address = $this->getMockServiceAddress(['getMapper', 'getList', 'updateLngLatTmz']);
        $s_address->expects($this->any())
            ->method('getMapper')
            ->will($this->returnSelf());
        $s_address->expects($this->any())
            ->method('getList')
            ->will($this->returnValue([$m_address]));
        $s_address->expects($this->any())
            ->method('updateLngLatTmz')
            ->will($this->returnValue('super'));

        $this->assertEquals([1 => 'super'], $s_address->initLngLat());
    }

    public function testCanUpdateLngLatTmz()
    {
        $m_address = new \Address\Model\Address();
        $m_address->setCity(new \Address\Model\City());
        $m_address->setDivision(new \Address\Model\Division());
        $m_address->setCountry(new \Address\Model\Country());

        $s_address = $this->getMockServiceAddress(['getMapper', 'update', 'getLngLat', 'getServiceGeoloc', 'getTimezone']);
        $s_address->expects($this->any())
            ->method('getMapper')
            ->will($this->returnSelf());
        $s_address->expects($this->any())
            ->method('update')
            ->will($this->returnValue('update'));

        $s_address->expects($this->any())
            ->method('getLngLat')
            ->will($this->returnValue(['lat' => 1, 'lng' => 2]));

        $s_address->expects($this->any())
            ->method('getServiceGeoloc')
            ->will($this->returnSelf());
        $s_address->expects($this->any())
            ->method('getTimezone')
            ->will($this->returnValue(['timeZoneId' => 'timeZoneId']));

        $this->assertEquals('update', $s_address->updateLngLatTmz($m_address));
        $this->assertEquals(1, $m_address->getLatitude());
        $this->assertEquals(2, $m_address->getLongitude());
        $this->assertEquals('timeZoneId', $m_address->getTimezone());
    }

    /**
     * @param array $methods
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockServiceAddress(array $methods = [])
    {
        $s_address = $this->getMockBuilder('\Address\Service\Address')
            ->setConstructorArgs([['prefix' => 'addr', 'name' => 'address']])       
            ->setMethods($methods)
            ->getMock();

        $s_address->setContainer($this->getApplicationServiceLocator());

        return $s_address;
    }
}
