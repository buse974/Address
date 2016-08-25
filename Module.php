<?php

namespace Address;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Address\Geoloc\Geoloc;

class Module implements ConfigProviderInterface
{
    protected $config;

    protected $config_name = 'address-conf';

    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array('namespaces' => array(__NAMESPACE__ => __DIR__.'/src/'.__NAMESPACE__)));
    }

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getConfigAddr(ServiceLocatorInterface $serviceLocator)
    {
        if (null !== $this->config) {
            return $this->config;
        }

        if (!$serviceLocator->has('Config')) {
            return false;
        }

        return $this->config = $serviceLocator->get('Config')[$this->config_name];
    }
    
    public function getServiceConfig()
    {
        return [
            'factories' => [
                'geoloc' => function ($container, $requestedName, $options) {
                    $conf = $container->get('config');
                    $conf_addr = $conf['address-conf'];
                
                    return new Geoloc($conf_addr, $conf[$conf_addr['geoloc']['adapter']]);
                },
            ],
        ];
    }
}
