<?php

namespace Address;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
}
