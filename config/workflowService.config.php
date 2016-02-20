<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsService;
use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsServiceFactory;

return [
    'workflow_zf2_service' => [
        'invokables'         => [

        ],
        'factories'          => [
            EntryToObjectsService::class            => EntryToObjectsServiceFactory::class,
        ],
        'abstract_factories' => [

        ]
    ],
];


