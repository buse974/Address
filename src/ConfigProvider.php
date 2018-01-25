<?php

namespace Address;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'address-conf' => array(
                'adapter' => 'db-adapter',
                'geoloc' => array(
                    'url' => 'https://maps.googleapis.com/maps/api/',
                    'adapter' => 'http-adapter',
                ),
            ),
            'dal-conf' => array(
                'adapter' => 'db-adapter',
                'cache' => 'storage_memcached',
                'log' => 'log-system',
                'namespace' => array(
                    'addr' => array(
                        'service' => 'Address\\Service',
                        'mapper' => 'Address\\Mapper',
                        'model' => 'Address\\Model',
                    ),
                ),
            ),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'factories' => [
                'geoloc' => function ($container, $requestedName, $options) {
                $conf = $container->get('config');
                $conf_addr = $conf['address-conf'];
                
                return new Geoloc\Geoloc($conf_addr, $conf[$conf_addr['geoloc']['adapter']]);
                },
            ],
            'abstract_factories' => [
                'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
                'Zend\Log\LoggerAbstractServiceFactory',
                'Zend\Db\Adapter\AdapterAbstractServiceFactory',
            ],
        ];
    }
}
