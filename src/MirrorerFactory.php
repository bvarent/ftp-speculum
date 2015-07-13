<?php

namespace Bvarent\FtpSpeculum;

use Zend\Config\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Creates a Mirrorer from the ZF2 Config and injects dependencies.
 *
 * @author bvarent <r.arents@bva-auctions.com>
 */
class MirrorerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Read the module configuration from the overall config into an options object.
        $totalConfig = $serviceLocator->get('config');
        /* @var $config Config */
        $options = new MirrorerOptions($totalConfig[Module::CONFIG_KEY][MirrorerOptions::MODULE_CONFIG_SUBKEY]);
        
        // Instantiate the service.
        $service = new Mirrorer($options);
        
        return $service;
    }
}
