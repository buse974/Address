<?php

namespace AddressTest\Mapper;

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
        $mapper = $this->getMockMapperMapper();

        $this->assertEquals('result', $mapper->getList([
            'search' => 'search',
            'country' => 1,
            'sort' => ['field' => 'field', 'direction' => 'direction'],
            'division' => 2,

        ]));
    }

    public function testGetCityByName()
    {
        $mapper = $this->getMockMapperMapper();

        $this->assertEquals('result', $mapper->getCityByName('city', 1, 2));
    }

    public function getMockMapperMapper()
    {
        $select = $this->getMock("Zend\Db\Sql\Select", ['columns', 'join', 'where']);

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

        $mapper = $this->getMock("\Address\Mapper\City", ['selectWith'], [$table]);

        $mapper->expects($this->once())
            ->method('selectWith')
            ->with($this->equalTo($select))
            ->will($this->returnValue('result'));

        return $mapper;
    }
}
