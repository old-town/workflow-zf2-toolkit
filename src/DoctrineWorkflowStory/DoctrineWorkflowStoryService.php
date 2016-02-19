<?php
/**
 * @link    https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory;

use Zend\Serializer\AdapterPluginManager as SerializerManager;
use OldTown\Workflow\ZF2\Service\Annotation as WFS;
use Zend\Serializer\Adapter\AdapterInterface as Serializer;
use OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory\ExtEntry;
use OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory\ObjectInfo;
use OldTown\Workflow\ZF2\Toolkit\Options\ModuleOptions;
use ReflectionClass;
use OldTown\Workflow\ZF2\ServiceEngine\WorkflowServiceInterface;

/**
 * Class DoctrineWorkflowStoryService
 *
 * @package OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory
 */
class DoctrineWorkflowStoryService
{
    /**
     * Псевдоним для объектов по умолчанию
     *
     * @var string
     */
    const DEFAULT_OBJECT = 'defaultObject';

    /**
     * Менеджер для получения адапторов отвечающих за сериализацию данных
     *
     * @var SerializerManager
     */
    protected $serializerManager;

    /**
     * Сериалайзер по умолчанию
     *
     * @var string
     */
    protected $serializerName = 'json';

    /**
     * Настройки модуля
     *
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * Сервис для работы с workflow
     *
     * @var WorkflowServiceInterface
     */
    protected $workflowService;

    /**
     * Сериалайзер
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * DoctrineWorkflowStoryService constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $initOptions = [
            array_key_exists('serializerManager', $options) ? $options['serializerManager'] : null,
            array_key_exists('moduleOptions', $options) ? $options['moduleOptions'] : null,
            array_key_exists('workflowService', $options) ? $options['workflowService'] : null
        ];
        call_user_func_array([$this, 'init'], $initOptions);
    }

    /**
     * @param SerializerManager        $serializerManager
     * @param ModuleOptions            $moduleOptions
     * @param WorkflowServiceInterface $workflowService
     */
    protected function init(SerializerManager $serializerManager, ModuleOptions $moduleOptions, WorkflowServiceInterface $workflowService)
    {
        $this->setSerializerManager($serializerManager);
        $this->setModuleOptions($moduleOptions);
        $this->setWorkflowService($workflowService);
    }

    /**
     * @WFS\ArgumentsMap(argumentsMap={
     *      @WFS\Map(fromArgName="entryParam", to="entry"),
     *      @WFS\Map(fromArgName="objectParam", to="object"),
     *      @WFS\Map(fromArgName="objectAliasParam", to="objectAlias"),
     *      @WFS\Map(fromArgName="storeParam", to="store")
     *})
     *
     *
     * @param ExtEntry               $entry
     * @param mixed                  $object
     * @param string                 $objectAlias
     * @param DoctrineWorkflowStory $store
     *
     * @throws Exception\InvalidWorkflowStoreException
     * @throws \OldTown\Workflow\Spi\Doctrine\Exception\DoctrineRuntimeException
     * @throws Exception\RuntimeException
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     * @throws \Zend\ServiceManager\Exception\RuntimeException
     * @throws \Zend\Serializer\Exception\ExceptionInterface
     */
    public function bindObjectToWorkflowEntry(ExtEntry $entry, $object, $objectAlias = self::DEFAULT_OBJECT, DoctrineWorkflowStory $store)
    {
        $em = $store->getEntityManager();

        $objectClass = get_class($object);
        $metadata = $em->getClassMetadata($objectClass);

        $serializerName = $this->getSerializerName();
        /** @var Serializer $serializer */
        $serializer = $this->getSerializerManager()->get($serializerName);

        $id = $metadata->getIdentifierValues($object);
        $prepareId = [];
        foreach ($id as $idField => $idValue) {
            $prepareId[$idField] = (string)$idValue;
        }
        $serializedId = $serializer->serialize($prepareId);

        $objectInfoClass = $this->getModuleOptions()->getEntityClassName('DoctrineWorkflowStory\\ObjectInfo');

        $r = new ReflectionClass($objectInfoClass);
        /** @var  ObjectInfo $objectInfo */
        $objectInfo = $r->newInstance();

        $objectInfo->setClassName($objectClass);
        $objectInfo->setObjectId($serializedId);
        $objectInfo->setAlias($objectAlias);
        $objectInfo->addEntry($entry);


        $entry->addObjectInfo($objectInfo);

        $em->persist($objectInfo);
        $em->flush();
    }

    /**
     * Востановить объект привязанный к процессу
     *
     * @WFS\ArgumentsMap(argumentsMap={
     *      @WFS\Map(fromArgName="entryParam", to="entry"),
     *      @WFS\Map(fromArgName="objectAliasParam", to="objectAlias"),
     *      @WFS\Map(fromArgName="storeParam", to="store")
     * })
     *
     * @WFS\ResultVariable(name="resultVariableName")
     *
     *
     * @param ExtEntry              $entry
     * @param string                $objectAlias
     * @param DoctrineWorkflowStory $store
     *
     * @return mixed
     *
     * @throws \OldTown\Workflow\Spi\Doctrine\Exception\DoctrineRuntimeException
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     * @throws \Zend\ServiceManager\Exception\RuntimeException
     * @throws \Zend\Serializer\Exception\ExceptionInterface
     * @throws Exception\InvalidRestoreObjectException
     * @throws Exception\InvalidArgumentException
     */
    public function restoreObjectBindingToEntry(ExtEntry $entry, $objectAlias = self::DEFAULT_OBJECT, DoctrineWorkflowStory $store)
    {
        $objectsInfo = $entry->getObjectsInfo();

        foreach ($objectsInfo as $objectInfo) {
            if ($objectAlias === $objectInfo->getAlias()) {
                $className = $objectInfo->getClassName();

                $em = $store->getEntityManager();

                $serializer = $this->getSerializer();

                $serializedId = $objectInfo->getObjectId();
                $id = $serializer->unserialize($serializedId);

                $object = $em->getRepository($className)->find($id);

                if (!is_object($object)) {
                    $errMsg = sprintf('Invalid restore object. Alias: %s. Class: %s. Id: %s', $objectAlias, $className, $serializedId);
                    throw new Exception\InvalidRestoreObjectException($errMsg);
                }

                return $object;
            }
        }

        $errMsg = sprintf('Invalid object alias: %s', $objectAlias);
        throw new Exception\InvalidArgumentException($errMsg);
    }

    /**
     * @return SerializerManager
     */
    public function getSerializerManager()
    {
        return $this->serializerManager;
    }

    /**
     * @param SerializerManager $serializerManager
     *
     * @return $this
     */
    public function setSerializerManager(SerializerManager $serializerManager)
    {
        $this->serializerManager = $serializerManager;

        return $this;
    }

    /**
     * @return string
     */
    public function getSerializerName()
    {
        return $this->serializerName;
    }

    /**
     * @param string $serializerName
     *
     * @return $this
     */
    public function setSerializerName($serializerName)
    {
        $this->serializerName = $serializerName;

        return $this;
    }

    /**
     * @return ModuleOptions
     */
    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    /**
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
     * Сервис для работы с workflow
     *
     * @return WorkflowServiceInterface
     */
    public function getWorkflowService()
    {
        return $this->workflowService;
    }

    /**
     * Устанавливает сервис для работы с workflow
     *
     * @param WorkflowServiceInterface $workflowService
     *
     * @return $this
     */
    public function setWorkflowService(WorkflowServiceInterface $workflowService)
    {
        $this->workflowService = $workflowService;

        return $this;
    }



    /**
     * @param $objectClassName
     * @param $objectId
     * @param $workflowName
     * @param $workflowManagerName
     */
    public function getEntryId($objectClassName, $objectId, $workflowName, $workflowManagerName)
    {
    }

    /**
     * @return Serializer
     *
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     * @throws \Zend\ServiceManager\Exception\RuntimeException
     */
    public function getSerializer()
    {
        if ($this->serializer) {
            return $this->serializer;
        }

        $serializerName = $this->getSerializerName();
        /** @var Serializer $serializer */
        $serializer = $this->getSerializerManager()->get($serializerName);
        $this->serializer = $serializer;

        return $this->serializer;
    }
}
