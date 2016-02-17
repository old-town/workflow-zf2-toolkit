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
     * DoctrineWorkflowStoryService constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $initOptions = [
            array_key_exists('serializerManager', $options) ? $options['serializerManager'] : null
        ];
        call_user_func_array([$this, 'init'], $initOptions);
    }

    /**
     * @param SerializerManager $serializerManager
     */
    protected function init(SerializerManager $serializerManager)
    {
        $this->setSerializerManager($serializerManager);
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
        $serializedId = $serializer->serialize($id);

        $objectInfo = new ObjectInfo();
        $objectInfo->setClassName($objectClass);
        $objectInfo->setObjectId($serializedId);
        $objectInfo->setAlias($objectAlias);
        $objectInfo->addEntry($entry);


        $entry->addObjectInfo($objectInfo);

        $em->persist($objectInfo);
        $em->flush();
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
}
