<?php
/**
 * @link    https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\WorkflowRunParams;

use Zend\Mvc\Application;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use OldTown\Workflow\ZF2\Service\Service\Manager as WorkflowServiceManager;
use OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory\DoctrineWorkflowStoryService;
use OldTown\Workflow\ZF2\Toolkit\Options\ModuleOptions;
use OldTown\Workflow\ZF2\ServiceEngine\Workflow as WorkflowService;

/**
 * Class EntryIdResolverFactory
 *
 * @package OldTown\Workflow\ZF2\Toolkit\WorkflowRunParams
 */
class EntryIdResolverFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return EntryIdResolver
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     * @throws \Zend\ServiceManager\Exception\RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var WorkflowServiceManager $wfServiceManager */
        $wfServiceManager = $serviceLocator->get(WorkflowServiceManager::class);

        /** @var DoctrineWorkflowStoryService $doctrineWorkflowStoryService */
        $doctrineWorkflowStoryService = $wfServiceManager->get(DoctrineWorkflowStoryService::class);
        $moduleOptions = $serviceLocator->get(ModuleOptions::class);

        $workflowService = $serviceLocator->get(WorkflowService::class);

        /** @var Application $app */
        $app = $serviceLocator->get('Application');
        $mvcEvent = $app->getMvcEvent();

        $serializer = $doctrineWorkflowStoryService->getSerializer();

        $options = [
            'doctrineWorkflowStoryService' => $doctrineWorkflowStoryService,
            'moduleOptions'                => $moduleOptions,
            'workflowService'              => $workflowService,
            'mvcEvent'                   => $mvcEvent,
            'serializer'                   => $serializer
        ];

        return new EntryIdResolver($options);
    }
}
