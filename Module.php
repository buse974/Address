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

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'addr_mapper_country' => function ($sm) {
                    $adapter = $sm->get($this->getConfigAddr($sm)['adapter']);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Address\Model\Country());
                    $tableGateway = new TableGateway('country', $adapter, null, $resultSetPrototype, new Sql($adapter, 'country'));

                    return new \Address\Mapper\Country($tableGateway);
                },
                'addr_mapper_division' => function ($sm) {
                    $adapter = $sm->get($this->getConfigAddr($sm)['adapter']);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Address\Model\Division\Relational());
                    $tableGateway = new TableGateway('division', $adapter, null, $resultSetPrototype, new Sql($adapter, 'division'));

                    return new \Address\Mapper\Division($tableGateway);
                },
                'addr_mapper_city' => function ($sm) {
                    $adapter = $sm->get($this->getConfigAddr($sm)['adapter']);

                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Address\Model\City\Relational());
                    $tableGateway = new TableGateway('city', $adapter, null, $resultSetPrototype, new Sql($adapter, 'city'));

                    return new \Address\Mapper\City($tableGateway);
                },
                'addr_mapper_address' => function ($sm) {
                    $adapter = $sm->get($this->getConfigAddr($sm)['adapter']);

                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new \Address\Model\Address\Relational());
                    $tableGateway = new TableGateway('address', $adapter, null, $resultSetPrototype, new Sql($adapter, 'address'));

                    return new \Address\Mapper\Address($tableGateway);
                },
                'addr_service_country' => function ($sm) {
                    return new \Address\Service\Country('addr_mapper_country');
                },
                'addr_service_division' => function ($sm) {
                    return new \Address\Service\Division('addr_mapper_division');
                },
                'addr_service_city' => function ($sm) {
                    return new \Address\Service\City('addr_mapper_city');
                },
                'addr_service_address' => function ($sm) {
                    return new \Address\Service\Address('addr_mapper_address');
                }
            ),
        );
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
