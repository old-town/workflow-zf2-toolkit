<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;

use OldTown\Workflow\ZF2\Toolkit\Validator\HttpMethod;
use OldTown\Workflow\ZF2\Toolkit\Validator\PrepareData;

return [
    'validators' => [
        'invokables' => [
            HttpMethod::class => HttpMethod::class,
            PrepareData::class => PrepareData::class
        ],
        'aliases' => [
            'httpMethod' => HttpMethod::class,
            'prepareData' => PrepareData::class,
        ]
    ]
];