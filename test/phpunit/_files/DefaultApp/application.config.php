<?php

use OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\TestPaths;
use \OldTown\Workflow\ZF2\Toolkit\PhpUnit\Utils\InitTestAppListener;

return [
    'modules'                 => [
        'DoctrineModule',
        'DoctrineORMModule',
        'OldTown\\Workflow\\ZF2',
        'OldTown\\Workflow\\ZF2\\Service',
        'OldTown\\Workflow\\ZF2\\Dispatch',
        'OldTown\\Workflow\\Doctrine\\ZF2',
        'OldTown\\Workflow\\ZF2\\Toolkit'
    ],
    'module_listener_options' => [
        'module_paths'      => [
            'OldTown\\Workflow\\ZF2\\Toolkit' => TestPaths::getPathToModule(),
        ],
        'config_glob_paths' => [
            __DIR__ . '/config/autoload/{{,*.}global,{,*.}local}.php',
        ],
    ],
    'service_manager'         => [
        'invokables' => [
            InitTestAppListener::class => InitTestAppListener::class
        ]
    ],
    'listeners'               => [
        InitTestAppListener::class
    ]
];
