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
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            // Ce code d'erreur n'est pas inclus dans error_reporting()
        return;
        }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>Mon ERREUR</b> [$errno] $errstr<br />\n";
        echo "  Erreur fatale sur la ligne $errline dans le fichier $errfile";
        echo ', PHP '.PHP_VERSION.' ('.PHP_OS.")<br />\n";
        echo "Arrêt...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>Mon ALERTE</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>Mon AVERTISSEMENT</b> [$errno] $errstr<br />\n";
        break;

    default:
        echo "Type d'erreur inconnu : [$errno] $errstr<br />\n";
        break;
    }

    /* Ne pas exécuter le gestionnaire interne de PHP */
    return false;
        });
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        ini_set('date.timezone', 'Europe/Paris');
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        //system('phing');
        static::initAutoloader();
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        /*$vendorPath = static::findParentPath('vendor');
        $zf2Path = $vendorPath.'/zendframework/zendframework/library/';
    
        include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';
        */
    \Zend\Loader\AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
            ),
        ));

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
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
