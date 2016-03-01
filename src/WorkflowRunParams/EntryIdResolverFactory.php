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
use OldTown\Workflow\ZF2\Toolkit\EntryToObjects\EntryToObjectsService;
use OldTown\Workflow\ZF2\Toolkit\Options\ModuleOptions;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;

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
     * @throws \Zend\Log\Exception\InvalidArgumentException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var WorkflowServiceManager $wfServiceManager */
        $wfServiceManager = $serviceLocator->get(WorkflowServiceManager::class);

        /** @var EntryToObjectsService $entryToObjectsService */
        $entryToObjectsService = $wfServiceManager->get(EntryToObjectsService::class);
        $moduleOptions = $serviceLocator->get(ModuleOptions::class);

        /** @var Application $app */
        $app = $serviceLocator->get('Application');
        $mvcEvent = $app->getMvcEvent();

        $logName = $moduleOptions->getLogName();
        if (null === $logName) {
            $log = new Logger();
            $writer = new Noop();
            $log->addWriter($writer);
        } else {
            $log = $serviceLocator->get($logName);
        }

        $options = [
            'entryToObjectsService' => $entryToObjectsService,
            'moduleOptions'         => $moduleOptions,
            'mvcEvent'              => $mvcEvent,
            'log'                   => $log
        ];

        return new EntryIdResolver($options);
    }
}
