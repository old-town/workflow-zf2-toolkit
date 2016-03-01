<?php
/**
 * @link    https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\EntryToObjects;

use OldTown\Workflow\ZF2\Toolkit\EntityRepository\DoctrineWorkflowStory\ExtEntryRepository;
use Zend\Serializer\AdapterPluginManager as SerializerManager;
use OldTown\Workflow\ZF2\Service\Annotation as WFS;
use Zend\Serializer\Adapter\AdapterInterface as Serializer;
use OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory\ExtEntry;
use OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory\ObjectInfo;
use OldTown\Workflow\ZF2\Toolkit\Options\ModuleOptions;
use ReflectionClass;
use OldTown\Workflow\ZF2\ServiceEngine\WorkflowServiceInterface;
use OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStory;

/**
 * Class EntryToObjectsService
 *
 * @package OldTown\Workflow\ZF2\Toolkit\EntryToObjects
 */
class EntryToObjectsService
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
     * Получене информации о процессе на основе данных о объектах привязанных к процессу
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
     * @return ExtEntry|null
     *
     * @throws Exception\InvalidGetEntryByObjectsInfoException
     * @throws \Zend\Serializer\Exception\ExceptionInterface
     */
    public function getEntryByObjectsInfo($managerName, $workflowName, array $objectsInfo = [])
    {
        try {
            $workflowManager = $this->getWorkflowService()->getWorkflowManager($managerName);


            $store = $workflowManager->getConfiguration()->getWorkflowStore();

            if (!$store instanceof DoctrineWorkflowStory) {
                $errMsg = sprintf('Workflow store not implement %s', DoctrineWorkflowStory::class);
                throw new Exception\InvalidWorkflowStoreException($errMsg);
            }
            $em = $store->getEntityManager();

            $serializer = $this->getSerializer();
            $objectHash = [];
            foreach ($objectsInfo as $entityClassName => $item) {
                $classMetadata = $em->getClassMetadata($entityClassName);
                $identifierMetadata = $classMetadata->getIdentifier();

                $id = [];

                foreach ($identifierMetadata as $propertyName) {
                    if (!array_key_exists($propertyName, $item)) {
                        $errMsg = sprintf('Property %s not found', $propertyName);
                        throw new Exception\InvalidGetEntryByObjectsInfoException($errMsg);
                    }
                    $id[$propertyName] = (string)$item[$propertyName];
                }

                $serializedId = $serializer->serialize($id);

                $hash = $entityClassName . '_' . $serializedId;
                $base64Hash = base64_encode($hash);

                $objectHash[$base64Hash] = $base64Hash;
            }

            $extEntryClassName = $this->getModuleOptions()->getEntityClassName('DoctrineWorkflowStory\ExtEntry');
            /** @var ExtEntryRepository $extEntryRepository */
            $extEntryRepository = $em->getRepository($extEntryClassName);

            $entry = $extEntryRepository->findEntryByObjectInfo($workflowName, $objectHash);
        } catch (\Exception $e) {
            throw new Exception\InvalidGetEntryByObjectsInfoException($e->getMessage(), $e->getCode(), $e);
        }
        return $entry;
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
