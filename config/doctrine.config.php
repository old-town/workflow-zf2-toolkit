<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

return [
    'doctrine' => [
        'driver' => [
            'entityToolkit' => [
                'paths' => stream_resolve_include_path(__DIR__ . '/../src/Entity'),
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
            ],
        ],
    ],
];
