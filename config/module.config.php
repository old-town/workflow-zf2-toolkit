<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

use OldTown\Workflow\ZF2\Toolkit\Options\ModuleOptions;

$config = [
    'workflow_zf2_toolkit' => [
        /**
         * Корневой неймспейс для сущностей.
         */
        'rootEntityNamespace' => 'OldTown\\Workflow\\ZF2\\Toolkit\\Entity\\',
        /**
         * Позволяет переопределить классы сущностей используемых в модуле OldTown\Workflow\ZF2\Toolkit.
         * Ключем является часть namespace сущности, начиная от OldTown\Workflow\ZF2\Toolkit\Entity
         * Например
         * DoctrineWorkflowStory\ExtEntry => MyClass::class
         */
        'entityMap' => [],
        /**
         * Метаданные используемые для получения id процесса wf(entryId).
         * Пример:
         * workflow_entry_to_object_metadata => [
         *     'key' => [
         *         'workflowManagerName' => $workflowManagerName,
         *         'workflowName'        => $workflowName,
         *         'map' => [
         *              'key1' => [
         *                  'entityClassName' => $entityClassName,
         *                  'routerParamName' => $routeParamName
         *              ]
         *          ]
         *     ]
         * ]
         *
         * Где:
         * - $workflowManagerName - имя менеджера workflow
         * - $workflowName        - имя используемого workflow
         * - $entityClassName     - имя класса сущности которая привязывается к процессу wf
         * - $routeParamName      - имя параметра роутера
         *
         *
         */
        'workflow_entry_to_object_metadata' => [

        ],
        /** Имя используемого логгера */
        ModuleOptions::LOG_NAME => null
    ]
];

return array_merge_recursive(
    include __DIR__ . '/doctrine.config.php',
    include __DIR__ . '/serviceManager.config.php',
    include __DIR__ . '/validator.config.php',
    include __DIR__ . '/workflowService.config.php',
    $config
);