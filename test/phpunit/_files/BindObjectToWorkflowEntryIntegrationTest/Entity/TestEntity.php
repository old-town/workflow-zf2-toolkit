<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\BindObjectToWorkflowEntryIntegrationTest\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Class TestEntity
 *
 * @ORM\Entity()
 * @ORM\Table(name="test")
 *
 * @package OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\BindObjectToWorkflowEntryIntegrationTest\Entity
 */
class TestEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(name="value", type="string", length=60)
     *
     * @var string
     */
    protected $value;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
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
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }


}
