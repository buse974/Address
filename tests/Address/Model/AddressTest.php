<?php
namespace AddressTest\Model;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AddressTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../config/application.config.php');
        
        parent::setUp();
    }

    public function testExchange()
    {
        $addr = new \Address\Model\Address();
        $data = [
            'id' => 'id', 
            'street_no' => 'street_no', 
            'street_type' => 'street_type', 
            'street_name' => 'street_name', 
            'building' => 'building', 
            'apartment' => 'apartment', 
            'door' => 'door', 
            'floor' => 'floor', 
            'city_id' => 'city_id', 
            'division_id' => 'division_id', 
            'country_id' => 'country_id',
            'city' => 'city',
            'division' => 'division',
            'country' => 'country',
            'longitude' => 'longitude', 
            'latitude' => 'latitude', 
            'timezone' => 'timezone'
        ];
        
        $copy = $data;
        $addr->exchangeArray($copy);
        $addr->setCity('city')->setCountry('country')->setDivision('division');
        $this->assertEquals($data, $addr->toArray());
    }

}
