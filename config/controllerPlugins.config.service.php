<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsControllerPlugin;
use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsControllerPluginFactory;

return [
    'controller_plugins' => [
        'factories' => [
            EntryToObjectsControllerPlugin::class => EntryToObjectsControllerPluginFactory::class
        ],
        'aliases' => [
            'workflowEntryToObjects' => EntryToObjectsControllerPlugin::class
        ]
    ],
];
