<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\BindObjectToWorkflowEntryIntegrationTest;

use OldTown\Workflow\Basic\BasicWorkflow;
use OldTown\Workflow\Loader\ArrayWorkflowFactory;
use OldTown\Workflow\Loader\XmlWorkflowFactory;
use OldTown\Workflow\Util\DefaultVariableResolver;
use \OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStory;
use OldTown\Workflow\Doctrine\ZF2\EntityManagerFactory;
use OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\TestPaths;

return [
    'doctrine' => [
        'entitymanager' => [
            'test' => [
                'configuration' => 'test',
                'connection'    => 'test',
            ]
        ],
        'connection' => [
            'test' => [
                'configuration' => 'test',
                'eventmanager'  => 'orm_default',
            ]
        ],
        'configuration' => [
            'test' => [
                'metadata_cache'    => 'array',
                'query_cache'       => 'array',
                'result_cache'      => 'array',
                'hydration_cache'   => 'array',
                'driver'            => 'test',
                'generate_proxies'  => true,

                'proxy_dir'         => TestPaths::getPathToDoctrineProxyDir(),
                'proxy_namespace'   => 'DoctrineORMModule\Proxy',
                'filters'           => [],
                'datetime_functions' => [],
                'string_functions' => [],
                'numeric_functions' => [],
                'second_level_cache' => []
            ]
        ],
        'driver' => [
            'test' => [
                'class'   => 'Doctrine\ORM\Mapping\Driver\DriverChain',
                'drivers' => [
                    'OldTown\\Workflow\\Spi\\Doctrine\\Entity' => 'WorkflowDoctrineEntity',
                    'OldTown\\Workflow\\ZF2\\Toolkit\\Entity' => 'entityToolkit'
                ]
            ]
        ]
    ],
    'workflow_zf2'    => [
        'configurations' => [
            'default' => [
                'persistence' => [
                    'name' => DoctrineWorkflowStory::class,
                    'options' => [
                        DoctrineWorkflowStory::ENTITY_MANAGER_FACTORY => [
                            DoctrineWorkflowStory::ENTITY_MANAGER_FACTORY_NAME => EntityManagerFactory::class,
                            DoctrineWorkflowStory::ENTITY_MANAGER_FACTORY_OPTIONS => [
                                EntityManagerFactory::ENTITY_MANAGER_NAME => 'doctrine.entitymanager.test'
                            ]
                        ]
                    ]
                ],
                'factory' => [
                    'name' => ArrayWorkflowFactory::class,
                    'options' => [
                        'reload' => true,
                        'workflows' => [
                            'test' => [
                                'location' => __DIR__ . '/test_workflow.xml'
                            ]
                        ]
                    ]
                ],
                'resolver' => DefaultVariableResolver::class,
            ]
        ],

        'managers' => [
            'testWorkflowManager' => [
                'configuration' => 'default',
                'name' => BasicWorkflow::class
            ]
        ]
    ]
];