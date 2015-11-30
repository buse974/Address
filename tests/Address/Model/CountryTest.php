<?php
namespace AddressTest\Model;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CountryTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../config/application.config.php');
        
        parent::setUp();
    }

    public function testExchange()
    {
        $country = new \Address\Model\Country();
        $data = [
            'id' => 'id', 
            'iso2' => 'iso2', 
            'short_name' => 'short_name', 
            'name' => 'name', 
            'iso3' => 'iso3', 
            'numcode' => 'numcode',
            'calling_code' => 'calling_code',
            'longitude' => 'longitude', 
            'latitude' => 'latitude'
        ];
        
        
        $copy = $data;
        $country->exchangeArray($copy);
       
        $this->assertEquals($data, $country->toArray());
    }

}
