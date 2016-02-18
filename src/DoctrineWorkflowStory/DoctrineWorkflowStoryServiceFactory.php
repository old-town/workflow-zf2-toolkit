<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use OldTown\Workflow\ZF2\ServiceEngine\Workflow;
use OldTown\Workflow\ZF2\Toolkit\Options\ModuleOptions;


/**
 * Class DoctrineWorkflowStoryServiceFactory
 *
 * @package OldTown\Workflow\ZF2\Toolkit\DoctrineWorkflowStory
 */
class DoctrineWorkflowStoryServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DoctrineWorkflowStoryService
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $appServiceLocator = $serviceLocator;
        if ($serviceLocator instanceof AbstractPluginManager) {
            $appServiceLocator = $serviceLocator->getServiceLocator();
        }



        $serializerManager = $appServiceLocator->get('SerializerAdapterManager');
        $moduleOptions = $appServiceLocator->get(ModuleOptions::class);
        $workflowService  = $appServiceLocator->get(Workflow::class);

        return new DoctrineWorkflowStoryService(
            [
                'serializerManager' => $serializerManager,
                'moduleOptions' => $moduleOptions,
                'workflowService' => $workflowService
            ]
        );
    }
}
