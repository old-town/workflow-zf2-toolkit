<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory;

use OldTown\Workflow\Spi\Doctrine\DoctrineWorkflowStory as BaseDoctrineWorkflowStory;
use OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory\Entry;

/**
 * Class DoctrineWorkflowStory
 *
 * @package OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory
 */
class DoctrineWorkflowStory extends BaseDoctrineWorkflowStory
{
    public function __construct()
    {
        $this->entityMap['entry'] = Entry::class;
    }

}
