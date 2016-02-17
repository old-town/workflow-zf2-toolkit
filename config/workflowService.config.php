<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

use OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStoryService;
use OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStoryServiceFactory;

return [
    'workflow_zf2_service' => [
        'invokables'         => [

        ],
        'factories'          => [
            DoctrineWorkflowStoryService::class            => DoctrineWorkflowStoryServiceFactory::class,
        ],
        'abstract_factories' => [

        ]
    ],
];


