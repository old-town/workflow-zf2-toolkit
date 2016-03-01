<?php
/**
 * @link    https://github.com/old-town/workflow-zf2-toolkit
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
use Zend\Log\LoggerInterface;
use Zend\Http\Request;

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
     * Имя параметра в конфиги модуля, по которому можно получить имя псевдонима менеджера wf
     *
     * @var string
     */
    const WORKFLOW_MANAGER_ALIAS = 'workflowManagerAlias';

    /**
     * Имя параметра в конфиге модуля, по которому можно получить имя wf
     *
     * @var string
     */
    const WORKFLOW_NAME = 'workflowName';

    /**
     * Имя параметра в конфиге модуля, по которому можно получить значение имени роутера
     *
     * @var string
     */
    const ROUTER_NAME = 'routeName';

    /**
     * Имя параметра в конфиге модуля, по которому можно конфиг описывающий какие классы и какие имена параметров
     * роуетера используется, для получения entryId
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
     * Имя параметра в карте(@see const MAP), по которому можно получить конфиг описывающий как для заданного свойства
     * сущности(свойство является либо первичным ключем, либо составной частью первичного ключа) получиить из
     * урл значение.
     *
     *
     * @var string
     */
    const IDENTIFIERS_MAP = 'identifiersMap';

    /**
     * Имя параметра в карте(@see const IDENTIFIERS_MAP), по которому можно получить имя свойства сущности, которое
     * является первичным ключем (или частью первичного ключа)
     *
     *
     * @var string
     */
    const PROPERTY_NAME = 'propertyName';

    /**
     * Имя параметра в карте(@see const IDENTIFIERS_MAP), по которому можно определить как получить значение свойства,
     * из параметра роутера, или из query
     *
     * @var string
     */
    const MODE = 'mode';

    /**
     * Значение для параметра mode (@see const MODE), определяет что значение берется из параметров роутера
     *
     * @var string
     */
    const MODE_ROUTER_PARAM = 'param';

    /**
     * Значение для параметра mode (@see const MODE), определяет что значение берется из query части url
     *
     * @var string
     */
    const MODE_QUERY = 'query';

    /**
     * Имя параметра в карте(@see const IDENTIFIERS_MAP), определяет имя параметра роутера или query параметра
     * содержащие значение для propertyName (@see const PROPERTY_NAME)
     *
     * @var string
     */
    const PARAM_NAME = 'paramName';

    /**
     * Набор допустимых значений для mode (@see const MODE)
     *
     * @var array
     */
    protected $accessMode = [
        self::MODE_ROUTER_PARAM => self::MODE_ROUTER_PARAM,
        self::MODE_ROUTER_PARAM => self::MODE_ROUTER_PARAM,
    ];

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
     * Логер
     *
     * @var LoggerInterface
     */
    protected $log;

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
            array_key_exists('mvcEvent', $options) ? $options['mvcEvent'] : null,
            array_key_exists('log', $options) ? $options['log'] : null
        ];
        call_user_func_array([$this, 'init'], $initOptions);
    }

    /**
     * @param EntryToObjectsService $entryToObjectsService
     * @param ModuleOptions         $moduleOptions
     * @param MvcEvent              $mvcEvent
     * @param LoggerInterface       $log
     */
    protected function init(
        EntryToObjectsService $entryToObjectsService,
        ModuleOptions $moduleOptions,
        MvcEvent $mvcEvent,
        LoggerInterface $log
    ) {
        $this->setEntryToObjectsService($entryToObjectsService);
        $this->setModuleOptions($moduleOptions);
        $this->setMvcEvent($mvcEvent);
        $this->setLog($log);
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
        $this->getLog()->info(
            'Getting "entryId" Workflow to run, based on the data of the object bound to the process'
        );

        $index = $this->getIndexMetadata();

        $managerName = $resolveEntryIdEvent->getManagerName();
        $managerAlias = $resolveEntryIdEvent->getManagerAlias();
        $workflowName = $resolveEntryIdEvent->getWorkflowName();

        $routeMatch = $this->getMvcEvent()->getRouteMatch();
        $routerName = $routeMatch->getMatchedRouteName();

        $indexKeys = $this->buildIndexKeys($routerName, $workflowName, $managerName, $managerAlias);

        $metadata = null;
        foreach ($indexKeys as $indexKey) {
            if (array_key_exists($indexKey, $index)) {
                $metadata = $index[$indexKey];
                break;
            }
        }

        if (null === $metadata) {
            $this->getLog()->info(
                'Metadata for "entryId" not found'
            );
            return null;
        }

        $objectsInfo = [];

        foreach ($metadata as $entityClassName => $metadataItem) {
            $objectsInfo[$entityClassName] = [];
            foreach ($metadataItem as $propertyName => $info) {
                $mode = $info[static::MODE];
                $paramName = $info[static::PARAM_NAME];

                $idValue = null;
                if (static::MODE_ROUTER_PARAM === $mode) {
                    $idValue = $routeMatch->getParam($paramName, null);
                }

                if (static::MODE_QUERY === $mode) {
                    $request = $this->getMvcEvent()->getRequest();
                    if ($request instanceof Request) {
                        $idValue = $request->getQuery($paramName, null);
                    }
                }

                if (null === $idValue) {
                    $errMsg = sprintf('Error getting the primary identifier for the entity\'s key. Source: %s. Value: %s', $mode, $paramName);
                    throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                }
                $objectsInfo[$entityClassName][$propertyName] = $idValue;
            }
        }

        $entry = $this->getEntryToObjectsService()->getEntryByObjectsInfo($managerName, $workflowName, $objectsInfo);

        if (null === $entry) {
            return null;
        }

        return $entry->getId();
    }

    /**
     * Подготавливает набор ключей для поиска в индексе
     *
     * @param string $routerName
     * @param string $workflowName
     * @param null   $managerName
     * @param null   $managerAlias
     *
     * @return array
     */
    public function buildIndexKeys($routerName, $workflowName, $managerName = null, $managerAlias = null)
    {
        $keys = [];

        $prefixes =[];
        if (null !== $managerAlias) {
            $prefixes[] = sprintf('alias_%s_%s_', $managerAlias, $workflowName);
        }
        if (null !== $managerName) {
            $prefixes[] = sprintf('name_%s_%s_', $managerName, $workflowName);
        }

        $prepareRouteParts = [];
        $stackRouteParts = explode('/', $routerName);

        for ($i = count($stackRouteParts); $i >= 1; $i--) {
            $routeParts = array_slice($stackRouteParts, 0, $i);
            $prepareRouteParts[] = implode('/', $routeParts);
        }

        foreach ($prefixes as $prefix) {
            foreach ($prepareRouteParts as $prepareRoutePart) {
                $keys[] = $prefix . $prepareRoutePart;
            }
        }

        return $keys;
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
            if (!array_key_exists(static::WORKFLOW_MANAGER_NAME, $metadataItem) && !array_key_exists(static::WORKFLOW_MANAGER_ALIAS, $metadataItem)) {
                $errMsg = sprintf(
                    'You must specify the %s or %s',
                    static::WORKFLOW_MANAGER_NAME,
                    static::WORKFLOW_MANAGER_ALIAS
                );
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }

            if (array_key_exists(static::WORKFLOW_MANAGER_NAME, $metadataItem) && array_key_exists(static::WORKFLOW_MANAGER_ALIAS, $metadataItem)) {
                $errMsg = sprintf(
                    'You can not specify both %s and %s',
                    static::WORKFLOW_MANAGER_NAME,
                    static::WORKFLOW_MANAGER_ALIAS
                );
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }

            if (!array_key_exists(static::WORKFLOW_NAME, $metadataItem)) {
                $errMsg = sprintf('there is no option %s', static::WORKFLOW_NAME);
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }
            $workflowName = $metadataItem[static::WORKFLOW_NAME];

            if (!array_key_exists(static::ROUTER_NAME, $metadataItem)) {
                $errMsg = sprintf('there is no option %s', static::ROUTER_NAME);
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }
            $routerName = $metadataItem[static::ROUTER_NAME];

            $prefix = '';
            if (array_key_exists(static::WORKFLOW_MANAGER_NAME, $metadataItem)) {
                $prefix = 'name_' . $metadataItem[static::WORKFLOW_MANAGER_NAME] . '_';
            }

            if (array_key_exists(static::WORKFLOW_MANAGER_ALIAS, $metadataItem)) {
                $prefix = 'alias_' . $metadataItem[static::WORKFLOW_MANAGER_ALIAS] . '_';
            }

            $uniqueKey = $prefix . $workflowName . '_' . $routerName;

            if (array_key_exists($uniqueKey, $index)) {
                $errMsg = sprintf('Index contains duplicate keys %s', $uniqueKey);
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }

            $index[$uniqueKey] = [];


            if (!array_key_exists(static::MAP, $metadataItem)) {
                $errMsg = sprintf('there is no option %s', static::MAP);
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }
            if (!is_array($metadataItem[static::MAP])) {
                $errMsg = sprintf('option %s is not array', static::MAP);
                throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
            }
            $map = $metadataItem[static::MAP];

            foreach ($map as $mapItem) {
                if (!array_key_exists(static::ENTITY_CLASS_NAME, $mapItem)) {
                    $errMsg = sprintf('there is no option %s', static::ENTITY_CLASS_NAME);
                    throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                }
                $entityClassName = $mapItem[static::ENTITY_CLASS_NAME];

                if (array_key_exists($entityClassName, $index[$uniqueKey])) {
                    $errMsg = sprintf('Metadata for entities already exist %s', $mapItem[static::ENTITY_CLASS_NAME]);
                    throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                }
                $index[$uniqueKey][$entityClassName] = [];

                if (!array_key_exists(static::IDENTIFIERS_MAP, $mapItem)) {
                    $errMsg = sprintf('there is no option %s', static::IDENTIFIERS_MAP);
                    throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                }
                if (!is_array($mapItem[static::IDENTIFIERS_MAP])) {
                    $errMsg = sprintf('option %s is not array', static::IDENTIFIERS_MAP);
                    throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                }

                $identifiersMap = $mapItem[static::IDENTIFIERS_MAP];

                foreach ($identifiersMap as $identifierItem) {
                    if (!array_key_exists(static::PROPERTY_NAME, $identifierItem)) {
                        $errMsg = sprintf('there is no option %s', static::PROPERTY_NAME);
                        throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                    }
                    $propertyName = $identifierItem[static::PROPERTY_NAME];

                    if (array_key_exists($propertyName, $index[$uniqueKey][$entityClassName])) {
                        $errMsg = sprintf('Metadata for property already exist %s', $propertyName);
                        throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                    }
                    $index[$uniqueKey][$entityClassName][$propertyName] = [];

                    $modeOriginal = array_key_exists(static::MODE, $identifierItem) ? $identifierItem[static::MODE] : static ::MODE_ROUTER_PARAM;
                    $mode = strtolower($modeOriginal);

                    if (!array_key_exists($mode, $this->accessMode)) {
                        $errMsg = sprintf('Invalid value for the "mode" %s', $mode);
                        throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                    }
                    $index[$uniqueKey][$entityClassName][$propertyName][static::MODE] = $mode;

                    if (!array_key_exists(static::PARAM_NAME, $identifierItem)) {
                        $errMsg = sprintf('there is no option %s', static::PARAM_NAME);
                        throw new Exception\InvalidWorkflowEntryToObjectMetadataException($errMsg);
                    }
                    $paramName = $identifierItem[static::PARAM_NAME];
                    $index[$uniqueKey][$entityClassName][$propertyName][static::PARAM_NAME] = $paramName;
                }
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


    /**
     * Устанавливает логер
     *
     * @return LoggerInterface
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Возвращает логер
     *
     * @param LoggerInterface $log
     *
     * @return $this
     */
    public function setLog(LoggerInterface $log)
    {
        $this->log = $log;

        return $this;
    }
}
