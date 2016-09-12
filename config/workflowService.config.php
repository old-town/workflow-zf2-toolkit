<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author   Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsService;
use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsServiceFactory;
use OldTown\Workflow\ZF2\Toolkit\Service\Comparator;

return [
    'workflow_zf2_service' => [
        'invokables'         => [
            Comparator::class => Comparator::class
        ],
        'factories'          => [
            EntryToObjectsService::class => EntryToObjectsServiceFactory::class,
        ],
        'abstract_factories' => [

        ]
    ],
];


