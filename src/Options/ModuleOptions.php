<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class ModuleOptions
 *
 * @package OldTown\Workflow\ZF2\Toolkit\Options
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * Наймспейс для сущностей.
     *
     * @var string
     */
    protected $rootEntityNamespace;

    /**
     * Карта доступа к сущностям
     *
     * @var array
     */
    protected $entityMap = [];

    /**
     * Метаданные для получения id процесса
     *
     * @var array
     */
    protected $workflowEntryToObjectMetadata = [];

    /**
     * @return string
     */
    public function getRootEntityNamespace()
    {
        return $this->rootEntityNamespace;
    }

    /**
     * @param string $rootEntityNamespace
     *
     * @return $this
     */
    public function setRootEntityNamespace($rootEntityNamespace)
    {
        $this->rootEntityNamespace = $rootEntityNamespace;

        return $this;
    }

    /**
     * @return array
     */
    public function getEntityMap()
    {
        return $this->entityMap;
    }

    /**
     * @param array $entityMap
     *
     * @return $this
     */
    public function setEntityMap(array $entityMap = [])
    {
        $this->entityMap = $entityMap;

        return $this;
    }

    /**
     * Возвращает класс сущности по ее имени
     *
     * @param string $entity
     *
     * @return array|string
     */
    public function getEntityClassName($entity)
    {
        if (array_key_exists($entity, $this->entityMap)) {
            return $this->entityMap;
        }

        return $this->rootEntityNamespace . $entity;
    }

    /**
     * Метаданные для получения id процесса
     *
     * @return array
     */
    public function getWorkflowEntryToObjectMetadata()
    {
        return $this->workflowEntryToObjectMetadata;
    }

    /**
     * Устанавливает метаданные для получения id процесса
     *
     * @param array $workflowEntryToObjectMetadata
     *
     * @return $this
     */
    public function setWorkflowEntryToObjectMetadata(array $workflowEntryToObjectMetadata = [])
    {
        $this->workflowEntryToObjectMetadata = $workflowEntryToObjectMetadata;

        return $this;
    }
}
