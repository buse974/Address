<?php

namespace AddressTest\Model;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CityTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__.'/../../config/application.config.php');

        parent::setUp();
    }

    public function testExchange()
    {
        $city = new \Address\Model\City();

        $reflector = new \ReflectionProperty('\Address\Model\City', 'array_prefix');
        $reflector->setAccessible(true);
        $reflector->setValue($city, []);

        $data = [
            'id' => 'id',
            'name' => 'name',
            'libelle' => 'libelle',
            'code' => 'code',
            'division_id' => 'division_id',
            'country_id' => 'country_id',
            'division' => 'division',
            'country' => 'country',
            'longitude' => 'longitude',
            'latitude' => 'latitude',
        ];

        $copy = $data;
        $city->exchangeArray($copy);
        $city->setCountry('country')->setDivision('division');

        $this->assertEquals($data, $city->toArray());
    }
}
