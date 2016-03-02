<?php
/**
 * @link    https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\Service;

use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use OldTown\Workflow\ZF2\ServiceEngine\Workflow;

/**
 * Class WorkflowToolsFactory
 *
 * @package OldTown\Workflow\ZF2\Toolkit\Service
 */
class WorkflowToolsFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return WorkflowTools
     *
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntryToObjectsService $entryToObjectsService */
        $entryToObjectsService = $serviceLocator->get(EntryToObjectsService::class);

        /** @var Workflow $workflowService */
        $workflowService = $serviceLocator->get(Workflow::class);

        $initOptions = [
            'entryToObjectsService' => $entryToObjectsService,
            'workflowService'       => $workflowService
        ];

        return new WorkflowTools($initOptions);
    }
}
