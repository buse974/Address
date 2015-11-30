<?php

namespace AddressTest\Model;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class DivisionTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__.'/../../config/application.config.php');

        parent::setUp();
    }

    public function testExchange()
    {
        $division = new \Address\Model\Division();

        $reflector = new \ReflectionProperty('\Address\Model\Division', 'array_prefix');
        $reflector->setAccessible(true);
        $reflector->setValue($division, []);

        $data = [
            'id' => 'id',
            'name' => 'name',
            'libelle' => 'libelle',
            'code' => 'code',
            'short_name' => 'short_name',
            'country_id' => 'country_id',
            'country' => 'country',
            'longitude' => 'longitude',
            'latitude' => 'latitude',
        ];

        $copy = $data;
        $division->exchangeArray($copy);
        $division->setCountry('country');

        $this->assertEquals($data, $division->toArray());
    }
}
