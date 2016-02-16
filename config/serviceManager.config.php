<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

use OldTown\Workflow\ZF2\Toolkit\Options\ModuleOptions;
use OldTown\Workflow\ZF2\Toolkit\Options\ModuleOptionsFactory;

return [
    'service_manager' => [
        'invokables'         => [

        ],
        'factories'          => [
            ModuleOptions::class            => ModuleOptionsFactory::class,
        ],
        'abstract_factories' => [

        ]
    ],
];


