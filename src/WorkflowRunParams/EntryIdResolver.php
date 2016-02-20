<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\WorkflowRunParams;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use OldTown\Workflow\ZF2\Dispatch\RunParamsHandler\RouteHandler;
use OldTown\Workflow\ZF2\Dispatch\RunParamsHandler\RouteHandler\ResolveEntryIdEventInterface;
use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsService;
use OldTown\Workflow\ZF2\Toolkit\Options\ModuleOptions;
use Zend\Mvc\MvcEvent;

/**
 * Class EntryIdResolver
 *
 * @package OldTown\Workflow\ZF2\Toolkit\WorkflowRunParams
 */
class EntryIdResolver extends AbstractListenerAggregate
{
    /**
     * Имя параметра в конфиги модуля, по которому можно получить имя менеджера wf
     *
     * @var string
     */
    const WORKFLOW_MANAGER_NAME = 'workflowManagerName';

    /**
     * Имя параметра в конфиге модуля, по которому можно получить имя wf
     *
     * @var string
     */
    const WORKFLOW_NAME = 'workflowName';

    /**
     * Имя параметра в конфиге модуля, по которому можно конфиг описывающий какие классы и какие имена параметров роуетера
     * используется, для получения entryId
     *
     * @var string
     */
    const MAP = 'map';

    /**
     * Имя параметра в карте(@see const MAP), по которому можно получить класс сущности
     *
     * @var string
     */
    const ENTITY_CLASS_NAME = 'entityClassName';

    /**
     * Имя параметра в карте(@see const MAP), по которому можно получить имя параметра роутера содержащего id сущности
     *
     * @var string
     */
    const ROUTER_PARAM_NAME = 'routerParamName';

    /**
     * Сервис реализующий функционал, для привязки процессов wf и информации о объектаъ
     *
     * @var EntryToObjectsService
     */
    protected $entryToObjectsService;

    /**
     * Настройки модуля
     *
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * Индекс для маппинга
     *
     * @var null|array
     */
    protected $indexMetadata;

    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * EntryIdResolver constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $initOptions = [
            array_key_exists('entryToObjectsService', $options) ? $options['entryToObjectsService'] : null,
            array_key_exists('moduleOptions', $options) ? $options['moduleOptions'] : null,
            array_key_exists('mvcEvent', $options) ? $options['mvcEvent'] : null
        ];
        call_user_func_array([$this, 'init'], $initOptions);
    }

    /**
     * @param EntryToObjectsService $entryToObjectsService
     * @param ModuleOptions                $moduleOptions
     * @param MvcEvent                     $mvcEvent
     */
    protected function init(
        EntryToObjectsService $entryToObjectsService,
        ModuleOptions $moduleOptions,
        MvcEvent $mvcEvent
    ) {
        $this->setEntryToObjectsService($entryToObjectsService);
        $this->setModuleOptions($moduleOptions);
        $this->setMvcEvent($mvcEvent);
    }

    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $events->getSharedManager()->attach(RouteHandler::class, ResolveEntryIdEventInterface::RESOLVE_ENTRY_ID_EVENT, [$this, 'onResolveEntryId'], 80);
    }

    /**
     * Обработчик содержащий логику получения entryId
     *
     * @param ResolveEntryIdEventInterface $resolveEntryIdEvent
     *
     *
     * @return null|string
     *
     * @throws \OldTown\Workflow\ZF2\Toolkit\EntryToObjects\Exception\InvalidGetEntryByObjectsInfoException
     * @throws \OldTown\Workflow\ZF2\Toolkit\WorkflowRunParams\Exception\InvalidWorkflowEntryToObjectMetadataException
     * @throws \Zend\Serializer\Exception\ExceptionInterface
     */
    public function onResolveEntryId(ResolveEntryIdEventInterface $resolveEntryIdEvent)
    {
        $index = $this->getIndexMetadata();

        $managerName = $resolveEntryIdEvent->getManagerName();
        if (!array_key_exists($managerName, $index)) {
            return null;
        }

        $workflowName = $resolveEntryIdEvent->getWorkflowName();
        if (!array_key_exists($workflowName, $index[$managerName]) || !is_array($index[$managerName][$workflowName])) {
            return null;
        }

        $routeMatch = $this->getMvcEvent()->getRouteMatch();
        $objectsInfo = [];
        foreach ($index[$managerName][$workflowName] as $entityClassName => $routerParamName) {
            $entityId = $routeMatch->getParam($routerParamName, null);
            if (null === $entityId) {
                return null;
            }

            $objectsInfo[$entityClassName] = $entityId;
        }

        $entry = $this->getEntryToObjectsService()->getEntryByObjectsInfo($managerName, $workflowName, $objectsInfo);

        if (null === $entry) {
            return null;
        }

        return $entry->getId();
    }

    /**
     * @return array|null
     *
     * @throws Exception\InvalidWorkflowEntryToObjectMetadataException
     */
    public function getIndexMetadata()
    {
        if (null !== $this->indexMetadata) {
            return $this->indexMetadata;
        }
        $metadata = $this->getModuleOptions()->getWorkflowEntryToObjectMetadata();

        $index = [];
        foreach ($metadata as $metadataItem) {
            if (!array_key_exists(static::WORKFLOW_MANAGER_NAME, $metadataItem)) {
                $errMsg = sprintf('there is no option %s', static::WORKFLOW_MANAGER_NAME);
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }
            $workflowManagerName = $metadataItem[static::WORKFLOW_MANAGER_NAME];

            if (!array_key_exists(static::WORKFLOW_NAME, $metadataItem)) {
                $errMsg = sprintf('there is no option %s', static::WORKFLOW_NAME);
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }
            $workflowName = $metadataItem[static::WORKFLOW_NAME];

            if (!array_key_exists(static::MAP, $metadataItem)) {
                $errMsg = sprintf('there is no option %s', static::MAP);
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }
            if (!is_array($metadataItem[static::MAP])) {
                $errMsg = sprintf('option %s is not array', static::MAP);
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }
            $map = $metadataItem[static::MAP];

            if (!array_key_exists($workflowManagerName, $index)) {
                $index[$workflowManagerName] = [];
            }
            if (!array_key_exists($workflowName, $index[$workflowManagerName])) {
                $index[$workflowManagerName][$workflowName] = [];
            }

            foreach ($map as $mapItem) {
                if (!array_key_exists(static::ENTITY_CLASS_NAME, $mapItem)) {
                    $errMsg = sprintf('there is no option %s', static::ENTITY_CLASS_NAME);
                    throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                }
                $entityClassName = $mapItem[static::ENTITY_CLASS_NAME];

                if (array_key_exists($entityClassName, $index[$workflowManagerName][$workflowName])) {
                    $errMsg = sprintf('Metadata for entities already exist %s', $mapItem[static::ENTITY_CLASS_NAME]);
                    throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                }

                if (!array_key_exists(static::ROUTER_PARAM_NAME, $mapItem)) {
                    $errMsg = sprintf('there is no option %s', static::ROUTER_PARAM_NAME);
                    throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                }
                $routerParamName = $mapItem[static::ROUTER_PARAM_NAME];

                $index[$workflowManagerName][$workflowName][$entityClassName] = $routerParamName;
            }
        }
        $this->indexMetadata = $index;

        return $this->indexMetadata;
    }

    /**
     * @param array|null $indexMetadata
     *
     * @return $this
     */
    public function setIndexMetadata(array $indexMetadata = null)
    {
        $this->indexMetadata = $indexMetadata;

        return $this;
    }


    /**
     * Сервис реализующий функционал, для привязки процессов wf и информации о объектаъ
     *
     * @return EntryToObjectsService
     */
    public function getEntryToObjectsService()
    {
        return $this->entryToObjectsService;
    }

    /**
     * Устанавливает сервис реализующий функционал, для привязки процессов wf и информации о объектаъ
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
     * Настройки модуля
     *
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    /**
     * Устанавливает настройки модуля
     *
     * @param ModuleOptions $moduleOptions
     *
     * @return $this
     */
    public function setModuleOptions(ModuleOptions $moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }

    /**
     * @return MvcEvent
     */
    public function getMvcEvent()
    {
        return $this->mvcEvent;
    }

    /**
     * @param MvcEvent $mvcEvent
     *
     * @return $this
     */
    public function setMvcEvent(MvcEvent $mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;

        return $this;
    }
}
