<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\EntityRepository\DoctrineWorkflowStory;

use Doctrine\ORM\EntityRepository;
use OldTown\Workflow\ZF2\Toolkit\Entity\DoctrineWorkflowStory\ExtEntry;


/**
 * Class ExtEntryRepository
 *
 * @package OldTown\Workflow\ZF2\Toolkit\EntityRepository\DoctrineWorkflowStory
 */
class ExtEntryRepository extends EntityRepository
{
    /**
     * @param       $workflowName
     * @param array $objectHash
     *
     * @return ExtEntry
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function findEntryByObjectInfo($workflowName, array $objectHash = [])
    {
        $entryClassName = ExtEntry::class;
        $excludeStatuses = [
            ExtEntry::KILLED,
            ExtEntry::COMPLETED,
            ExtEntry::UNKNOWN
        ];

        $dql = "
          SELECT
            entry
          FROM {$entryClassName} entry
          JOIN entry.objectsInfo objectInfo
          WHERE
              entry.workflowName = :workflowName
                AND
              objectInfo.hash IN (:hash)
                AND
              entry.state NOT IN (:statuses)
          GROUP BY entry.id
          ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('workflowName', $workflowName);
        $query->setParameter('hash', array_values($objectHash));
        $query->setParameter('statuses', $excludeStatuses, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        return $query->getSingleResult();
    }
}
