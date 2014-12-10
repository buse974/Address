<?php

namespace Address;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Address\Db\ResultSet\ResultSet;
use Address\Db\TableGateway\TableGateway;
use Address\Db\Sql\Sql;

class Module implements ConfigProviderInterface
{
    protected $config;
    protected $config_name = 'address-conf';

    public function getAutoloaderConfig()
    {
        return array(
                'Zend\Loader\StandardAutoloader' => array(
                    'namespaces' => array(
                        __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                	),
                ),
                'Zend\Loader\ClassMapAutoloader' => array(
                    __DIR__ . '/autoload_classmap.php',
                ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
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
