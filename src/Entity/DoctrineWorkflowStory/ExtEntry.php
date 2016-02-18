<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory;

use OldTown\Workflow\Spi\Doctrine\Entity\AbstractEntry;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Entry
 *
 * @ORM\Entity()
 * @ORM\Table(name="wf_entry")
 *
 * @package OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory
 */
class ExtEntry extends AbstractEntry
{

    /**
     * Информация о объектах которые привязанны к процессу
     *
     * @ORM\ManyToMany(targetEntity="ObjectInfo", inversedBy="entries")
     * @ORM\JoinTable(
     *     name="wf_entry_to_object",
     *     joinColumns={
     *         @ORM\JoinColumn(name="entry_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     *     }
     * )
     *
     * @var ObjectInfo[]|ArrayCollection
     */
    protected $objectsInfo;

    /**
     * ExtEntry constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->objectsInfo = new ArrayCollection();
    }


    /**
     * Возвращает инмформацию о всех объектах привязанных к данному процессу
     *
     * @return ArrayCollection|ObjectInfo[]
     */
    public function getObjectsInfo()
    {
        return $this->objectsInfo;
    }

    /**
     * Добавляет информацию о объекте который привязан к процессу wf
     *
     * @param ObjectInfo $objectInfo
     *
     * @return $this
     */
    public function addObjectInfo(ObjectInfo $objectInfo)
    {
        if (!$this->getObjectsInfo()->contains($objectInfo)) {
            $this->getObjectsInfo()->add($objectInfo);
        }

        return $this;
    }
}
