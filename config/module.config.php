<?php

namespace Bvarent\FtpSpeculum;

// $modulePath should have been defined by the file which included this file.
/* @var $modulePath string */
if (!isset($modulePath)) {
    $modulePath = dirname(__DIR__);
}

return [
    // Defaults for the configuration of this module.
    Module::CONFIG_KEY => [
        MirrorerOptions::MODULE_CONFIG_SUBKEY => MirrorerOptions::defaults(),
    ],
    
    // Services
    'service_manager' => [
        'aliases' => [
            'FtpMirrorer' => Mirrorer::class,
        ],
        'factories' => [
            Mirrorer::class => MirrorerFactory::class,
        ],
    ],
    
    // Controllers.
    'controllers' => [
        'invokables' => [
            ConsoleController::class => ConsoleController::class,
        ],
    ],
    
    // Console (CLI) commands.
    'console' => [
        'router' => [
            'routes' => [
                Module::CONFIG_KEY => [
                    'type' => 'simple',
                    'options' => [
                        'route' => Module::CONFIG_KEY,
                        'defaults' => [
                            'controller' => ConsoleController::class,
                            'action' => 'mirror',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
