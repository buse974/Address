<?php
namespace AddressTest\Mapper;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AddressTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../config/application.config.php');
        
        parent::setUp();
    }

    public function testGet()
    {
        $addr = $this->getMockMapperAddress();
        
        $this->assertEquals('result', $addr->get([]));
    }

    public function testGetList()
    {
        $addr = $this->getMockMapperAddress();
        
        $this->assertEquals('result', $addr->getList());
    }

    public function testSelectByArray()
    {
        $addr = $this->getMockMapperAddress();
        
        $address = ['city' => 1, 'division' => 2, 'country' => 3, 'street_no' => 'street_no','street_type' => 'street_type','street_name' => 'street_name','floor' => 'floor','door' => 'door','apartment' => 'apartment','building' => 'building'];
        
        $this->assertEquals('result', $addr->selectByArray($address));
    }
    
    public function testSelectByArrayEmpty()
    {
        $addr = $this->getMockMapperAddress();
    
        $this->assertEquals('result', $addr->selectByArray([],1,2,3));
    }

    public function getMockMapperAddress()
    {
        $select = $this->getMock("Zend\Db\Sql\Select", ['columns','join','where']);
        
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
            ->setMethods(['getSql','select'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $table->expects($this->once())
            ->method('getSql')
            ->will($this->returnSelf());
        $table->expects($this->once())
            ->method('select')
            ->will($this->returnValue($select));
        
        $addr = $this->getMock("\Address\Mapper\Address", ['selectWith'], [$table]);
        
        $addr->expects($this->once())
            ->method('selectWith')
            ->with($this->equalTo($select))
            ->will($this->returnValue('result'));
        
        return $addr;
    }
}
