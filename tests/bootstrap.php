<?php

namespace DalTest;

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

/**
 * Test bootstrap, for setting up autoloading.
 */
class bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        ini_set('date.timezone', 'Europe/Paris');
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        static::initAutoloader();
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        \Zend\Loader\AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
            ),
        ));

        $smConfig = new ServiceManagerConfig([]);
        $serviceManager = new ServiceManager();
        $smConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', include __DIR__.'/config/application.config.php');
        $serviceManager->get('ModuleManager')->loadModules();
        
        static::$serviceManager = $serviceManager;
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir.'/'.$path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }

        return $dir.'/'.$path;
    }
}

Bootstrap::init();
