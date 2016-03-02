<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\Service;

use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsService;
use OldTown\Workflow\ZF2\ServiceEngine\Workflow as WorkflowService;

/**
 * Class WorkflowTools
 *
 * @package OldTown\Workflow\ZF2\Toolkit\Validator
 */
class WorkflowTools
{
    /**
     * Сервис позволяющий получать процесс workflow, на основе данных привязанной к нему сущности
     *
     * @var EntryToObjectsService
     */
    protected $entryToObjectsService;

    /**
     * Сервис для работы с wf
     *
     * @var WorkflowService
     */
    protected $workflowService;

    /**
     * DoctrineWorkflowStoryService constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $initOptions = [
            array_key_exists('entryToObjectsService', $options) ? $options['entryToObjectsService'] : null,
            array_key_exists('workflowService', $options) ? $options['workflowService'] : null,
        ];
        call_user_func_array([$this, 'init'], $initOptions);
    }

    /**
     * Получене информации о доступных действиях на основе данных о объектах привязанных к процессу
     *
     * Структура objectsInfo
     * $objectsInfo = [
     *      $entityClassName => [
     *          $propertyName => $idValue
     *      ]
     * ]
     *
     * Где:
     * $entityClassName - имя класса сущности которая привязана к процессу workflow
     * $propertyName    - имя свойства сущности являющееся первичным ключем (или часть составного первичного ключа)
     * $idValue         - значение $propertyName
     *
     *
     * @param string $managerName
     * @param string $workflowName
     * @param array  $objectsInfo
     *
     * @return array|\OldTown\Workflow\Loader\ActionDescriptor[]
     *
     * @throws \OldTown\Workflow\ZF2\Toolkit\EntryToObjects\Exception\InvalidGetEntryByObjectsInfoException
     * @throws \Zend\Serializer\Exception\ExceptionInterface
     * @throws \OldTown\Workflow\ZF2\ServiceEngine\Exception\InvalidManagerNameException
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \OldTown\Workflow\ZF2\ServiceEngine\Exception\InvalidWorkflowManagerException
     * @throws \OldTown\Workflow\Exception\ArgumentNotNumericException
     */
    public function getAvailableActions($managerName, $workflowName, array $objectsInfo = [])
    {
        $entry = $this->getEntryToObjectsService()->getEntryByObjectsInfo($managerName, $workflowName, $objectsInfo);

        $entryId = $entry->getId();

        return $this->getWorkflowService()->getAvailableActions($managerName, $entryId);
    }

    /**
     * Получене информации о именах доступных действиях на основе данных о объектах привязанных к процессу
     *
     * Структура objectsInfo
     * $objectsInfo = [
     *      $entityClassName => [
     *          $propertyName => $idValue
     *      ]
     * ]
     *
     * Где:
     * $entityClassName - имя класса сущности которая привязана к процессу workflow
     * $propertyName    - имя свойства сущности являющееся первичным ключем (или часть составного первичного ключа)
     * $idValue         - значение $propertyName
     *
     *
     * @param string $managerName
     * @param string $workflowName
     * @param array  $objectsInfo
     *
     * @return array
     *
     * @throws \OldTown\Workflow\ZF2\Toolkit\EntryToObjects\Exception\InvalidGetEntryByObjectsInfoException
     * @throws \Zend\Serializer\Exception\ExceptionInterface
     * @throws \OldTown\Workflow\ZF2\ServiceEngine\Exception\InvalidManagerNameException
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \OldTown\Workflow\ZF2\ServiceEngine\Exception\InvalidWorkflowManagerException
     * @throws \OldTown\Workflow\Exception\ArgumentNotNumericException
     */
    public function getAvailableActionNames($managerName, $workflowName, array $objectsInfo = [])
    {
        $actions = $this->getAvailableActions($managerName, $workflowName, $objectsInfo);

        $names = [];

        foreach ($actions as $action) {
            $name = $action->getName();
            $names[$name] = $name;
        }

        return $names;
    }

    /**
     * Инициализация
     *
     * @param EntryToObjectsService $entryToObjectsService
     * @param WorkflowService       $workflowService
     */
    protected function init(EntryToObjectsService $entryToObjectsService, WorkflowService $workflowService)
    {
        $this->setEntryToObjectsService($entryToObjectsService);
        $this->setWorkflowService($workflowService);
    }

    /**
     * Сервис позволяющий получать процесс workflow, на основе данных привязанной к нему сущности
     *
     * @return EntryToObjectsService
     */
    public function getEntryToObjectsService()
    {
        return $this->entryToObjectsService;
    }

    /**
     * Устанавливает сервис позволяющий получать процесс workflow, на основе данных привязанной к нему сущности
     *
     * @param EntryToObjectsService $entryToObjectsService
     *
     * @return $this
     */
    public function setEntryToObjectsService(EntryToObjectsService $entryToObjectsService)
    {
        $this->entryToObjectsService = $entryToObjectsService;

        return $this;
    }

    /**
     * Сервис для работы с wf
     *
     * @return WorkflowService
     */
    public function getWorkflowService()
    {
        return $this->workflowService;
    }

    /**
     * Устанавливает сервис для работы с wf
     *
     * @param WorkflowService $workflowService
     *
     * @return $this
     */
    public function setWorkflowService(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;

        return $this;
    }
}
