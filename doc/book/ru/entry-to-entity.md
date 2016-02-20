# Сущности и процессы wf

Как правило состояния процесса wf коррелируют с состояниями одной или нескольких сущностей. На практике часто будут 
возникать следующие задачи:

* Связать процесс wf с одной или несколькими сущностями доктрины
* Получить сущности привязанные к конкретному процессу wf
* Зная информацию о сущностях (класс и значение первичного ключа) получить id процесса workflow

Для решения этих задач реализовано хранилище состояния wf поддерживающий данный функционал (\OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStory).

# Использование хранилища wf поддерживающего привязку информации о объекта к процессу

## Конфигурирование

>* Убедится что к драйверам метаданных доктрины добавлены соответствующие строки:
>```php
>'doctrine' => [
>    'driver' => [
>        'orm_default' => [
>            'drivers' => [
>                'OldTown\\Workflow\\Spi\\Doctrine\\Entity' => 'WorkflowDoctrineEntity',
>                'OldTown\\Workflow\\ZF2\\Toolkit\\Entity' => 'entityToolkit'
>            ],
>        ],
>    ],
>]
>```
>
>* При описание конфигурации менеджера wf корректно указано хранилище(\OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStory):
>```php
>use 
>
>use \OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStory
>
>'workflow_zf2'    => [
>    'configurations' => [
>        'default' => [
>            'persistence' => [
>                'name' => DoctrineWorkflowStory::class,
>                'options' => [
>                    DoctrineWorkflowStory::ENTITY_MANAGER_FACTORY => [
>                        DoctrineWorkflowStory::ENTITY_MANAGER_FACTORY_NAME => EntityManagerFactory::class,
>                        DoctrineWorkflowStory::ENTITY_MANAGER_FACTORY_OPTIONS => [
>                            EntityManagerFactory::ENTITY_MANAGER_NAME => 'doctrine.entitymanager.test'
>                        ]
>                    ]
>                ]
>            ],
>            'factory' => [
>                'name' => ArrayWorkflowFactory::class,
>                'options' => [
>                    'reload' => true,
>                    'workflows' => [
>                        'test' => [
>                            'location' => __DIR__ . '/test_workflow.xml'
>                        ]
>                    ]
>                ]
>            ],
>            'resolver' => DefaultVariableResolver::class,
>        ]
>    ],
>
>    'managers' => [
>        'testWorkflowManager' => [
>            'configuration' => 'default',
>            'name' => BasicWorkflow::class
>        ]
>    ]
>],
>
```


