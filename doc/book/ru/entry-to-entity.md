# Сущности и процессы wf

Как правило состояния процесса wf коррелируют с состояниями одной или нескольких сущностей. На практике часто будут 
возникать следующие задачи:

* Связать процесс wf с одной или несколькими сущностями доктрины
* Получить сущности привязанные к конкретному процессу wf
* Зная информацию о сущностях (класс и значение первичного ключа) получить id процесса workflow

Для решения этих задач реализовано хранилище состояния wf поддерживающий данный функционал (\OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStory).

# Использование хранилища wf поддерживающего привязку информации о объекта к процессу

## Конфигурирование

* Убедится что к драйверам метаданных доктрины добавлены соответствующие строки:

```php
'doctrine' => [
    'driver' => [
        'orm_default' => [
            'drivers' => [
                'OldTown\\Workflow\\Spi\\Doctrine\\Entity' => 'WorkflowDoctrineEntity',
                'OldTown\\Workflow\\ZF2\\Toolkit\\Entity' => 'entityToolkit'
            ],
        ],
    ],
]
```

* При описание конфигурации менеджера wf корректно указано хранилище(\OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStory):

```php

use \OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStory

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
],

```

# Сервис для работы с процессом wf и сущностями \OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsService
 
 Сервис содержит в себе базовый функционал для привязки объектов к процессу, востановлению объектов на основе данных о процессе,
 получение процесса по данным о привязанном объекте. Сервис предназначен для использования в wf.
 
 Метод|Описание 
 ---------------------------|-------------------------------------------------------------------------------------------
 bindObjectToWorkflowEntry  |Привязать объект к процессу
 restoreObjectBindingToEntry|Востоновить объект привязанный к процессу
 getEntryByObjectsInfo      |Получить информацию о процессе на основе данных о объектах
 
 Для удобной работы с данным сервисом в контроллерах, добавлен плагин для контроллера \OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsControllerPlugin,
 доступный по псевдониму workflowEntryToObjects. Этот плагин является оберткой для сервиса.
 
# Автоматическое получение id процесса на основе данных о привязанных объектах.

В модуле реализован обработчик \OldTown\Workflow\ZF2\Toolkit\WorkflowRunParams\EntryIdResolver события workflow.dispatch.resolveEntryId
сервиса \OldTown\Workflow\ZF2\Dispatch\RunParamsHandler\RouteHandler\ResolveEntryIdEvent.

Данный обработчик позволяет на основе конфигов получить значение id процесса wf, на основе значения id сущности.

Для осуществления процесса получения id необходимо в конфигах описать метаданные для маппинга:

```php
'workflow_zf2_toolkit' => [
    'workflow_entry_to_object_metadata' => [
        'key' => [
            'workflowManagerName'  => $workflowManagerName,
            'workflowManagerAlias' => $workflowManagerAlias,
            'workflowName'         => $workflowName,
            'routeName'            => $routeName,
            'map'                  => [
                'key1' => [
                    'entityClassName' => $entityClassName,
                    'identifiersMap'  => [
                        'key2' => [
                            'propertyName' => $propertyName,
                            'mode'         => 'param|query',
                            'paramName'    => $paramName
                        ]
                    ]
                ]
            ]
        ]
    ]
]
```

Где:

Параметр             |Описание
-----------------------------------
$workflowManagerName |имя менеджера workflow
$workflowManagerAlias|псевдоним менеджера workflow
$workflowName        |имя используемого workflow
$entityClassName     |имя класса сущности которая привязывается к процессу wf
$routeName           |имя роутера
$propertyName        |имя свойства сущности(должен быть геттер и зеттер) являющиеся первичным ключем(или частью первичного ключа)
$paramName           |имя параметра, по которому можно получить значение для $propertyName

Необходимо помнить, что можно указать либо workflowManagerName, либо workflowManagerAlias. В случае если укзать оба 
этих параметра, либо не указать ни один из них, будет брошено исключение.

key, key1, key2 - любые уникальные значения. Используются что бы была возможность перегрузить конфиг на уровне
приложения

 
 
 
 
 


