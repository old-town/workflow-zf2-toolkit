<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

$config = [
    'workflow_zf2_toolkit' => [
        'rootEntityNamespace' => 'OldTown\\Workflow\\ZF2\\Toolkit\\Entity\\',
        'entityMap' => []
    ]
];

return array_merge_recursive(
    include __DIR__ . '/doctrine.config.php',
    include __DIR__ . '/serviceManager.config.php',
    include __DIR__ . '/validator.config.php',
    include __DIR__ . '/workflowService.config.php',
    $config
);