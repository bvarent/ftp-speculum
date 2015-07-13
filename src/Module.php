<?php

namespace bvarent\FtpSpeculum;

use Zend\Console\Adapter\AdapterInterface;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Uri\Uri;
use Zend\Uri\UriFactory;

class Module implements
AutoloaderProviderInterface, ConfigProviderInterface, ConsoleBannerProviderInterface, ConsoleUsageProviderInterface, BootstrapListenerInterface
{
    
    /**
     * A human readable name for this module.
     */
    const MODULE_NAME = 'BVA Ftp Speculum';
    
    /**
     * The key to use in the global ZF2 config to identify this module.
     */
    const CONFIG_KEY = 'ftp-speculum';

    /**
     * Gives the path to the root directory of this module.
     * @return string
     */
    protected function getModulePath()
    {
        // Assume this file is in {module root path}/src.
        return dirname(__DIR__);
    }

    public function getAutoloaderConfig()
    {
        // This is a backup in case composer's autoloader is not in use or does
        //  not know about this module.
        // Presumes that the current class is __NAMESPACE__\Module, living in __DIR__.
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        $modulePath = $this->getModulePath();

        return include $modulePath . '/config/module.config.php';
    }
    
    /**
     * Makes \Zend\Uri recognize the ftp:// scheme.
     */
    protected function registerFtpUriScheme()
    {
        UriFactory::registerScheme('ftp', Uri::class);
    }

    public function onBootstrap(EventInterface $e)
    {
        $this->registerFtpUriScheme();
    }

    public function getConsoleBanner(AdapterInterface $console)
    {
        return Module::MODULE_NAME;
    }

    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            Module::CONFIG_KEY => "Invoke the mirrorer given the predefined module's config.",
        );
    }

}
