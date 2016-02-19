<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Object
 *
 * @ORM\Entity()
 * @ORM\Table(
 *     name="wf_object_info",
 *     indexes={
 *         @ORM\Index(name="hash", columns={"hash"})
 *     }
 * )
 *
 * @package OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory
 */
class ObjectInfo
{
    /**
     * Уникальный id данных о объекте
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var string
     */
    protected $id;

    /**
     * Имя класса объекта
     *
     * @ORM\Column(name="class_name", type="string", length=180)
     *
     *
     * @var string
     */
    protected $className;

    /**
     * id объекта
     *
     * @ORM\Column(name="object_id", type="string", length=50)
     *
     *
     * @var string
     */
    protected $objectId;

    /**
     * Псевдоним по которому можно обратиться к объекту
     *
     * @ORM\Column(name="alias", type="string", length=50)
     *
     *
     * @var string
     */
    protected $alias;

    /**
     * Все процессы workflow которые связанны с данным процесом
     *
     * @ORM\ManyToMany(targetEntity="ExtEntry", mappedBy="objectsInfo")
     *
     * @var ExtEntry[]|ArrayCollection
     */
    protected $entries;

    /**
     * Ключ для поиска
     *
     * @ORM\Column(name="hash", type="string", length=255)
     *
     * @var string
     */
    protected $hash;

    /**
     * ObjectInfo constructor.
     */
    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    /**
     * Уникальный id данных о объект
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Возвращает уникальный id данных о объект
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Имя класса объекта
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Устанавливает имя класса объекта
     *
     * @param string $className
     *
     * @return $this
     */
    public function setClassName($className)
    {
        $this->className = $className;
        $this->updateHash();

        return $this;
    }

    /**
     * id объекта
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Устанвливает id объекта
     *
     * @param string $objectId
     *
     * @return $this
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        $this->updateHash();

        return $this;
    }

    /**
     * Псевдоним по которому можно обратиться к объекту
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Устанавливает пседвоним по которому можно обратиться к объекту
     *
     * @param string $alias
     *
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Возвращает все процессы wf,в которых участвует объект
     *
     * @return ArrayCollection|ExtEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Добавляет процесс wf, в котором задействован объект
     *
     * @param ExtEntry $entry
     *
     * @return $this
     */
    public function addEntry(ExtEntry $entry)
    {
        if (!$this->getEntries()->contains($entry)) {
            $this->getEntries()->add($entry);
        }

        return $this;
    }

    /**
     * генерация хеша
     *
     * @return void
     */
    protected function updateHash()
    {
        $hash = $this->getClassName() . '_' . $this->getObjectId();
        $base64Hash = base64_encode($hash);

        $this->hash = $base64Hash;
    }
}
