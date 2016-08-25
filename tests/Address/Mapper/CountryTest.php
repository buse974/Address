<?php

namespace AddressTest\Mapper;

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
        $mapper = $this->getMockMapperMapper();

        $this->assertEquals('result', $mapper->getList(['search' => 'search']));
    }

    public function testGetCountryByName()
    {
        $mapper = $this->getMockMapperMapper();

        $this->assertEquals('result', $mapper->getCountryByName('country'));
    }

    public function getMockMapperMapper()
    {
        $select = $this->getMockBuilder('Dal\Db\Sql\Select')->setMethods(['columns', 'join', 'where'])->getMock();
        $select->expects($this->once())
            ->method('columns')
            ->will($this->returnSelf());

        $select->expects($this->any())
            ->method('join')
            ->will($this->returnSelf());

        $select->expects($this->any())
            ->method('where')
            ->will($this->returnSelf());

        $table = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->setMethods(['getSql', 'select'])
            ->disableOriginalConstructor()
            ->getMock();

        $table->expects($this->once())
            ->method('getSql')
            ->will($this->returnSelf());
        $table->expects($this->once())
            ->method('select')
            ->will($this->returnValue($select));

        $mapper = $this->getMockBuilder('Address\Mapper\Country')
            ->setConstructorArgs([$table])
            ->setMethods(['selectWith'])
            ->getMock();
        
        $mapper->expects($this->once())
            ->method('selectWith')
            ->with($this->equalTo($select))
            ->will($this->returnValue('result'));

        return $mapper;
    }
}
