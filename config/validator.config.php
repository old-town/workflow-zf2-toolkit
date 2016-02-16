<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

use OldTown\Workflow\ZF2\Toolkit\Validator\HttpMethod;

return [
    'validators' => [
        'invokables' => [
            HttpMethod::class => HttpMethod::class
        ],
        'aliases' => [
            'httpMethod' => HttpMethod::class
        ]
    ]
];