<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit;


use OldTown\Workflow\ZF2\Toolkit\WorkflowRunParams\EntryIdResolver;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;


/**
 * Class Module
 *
 * @package OldTown\Workflow\ZF2\Toolkit
 */
class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    AutoloaderProviderInterface,
    DependencyIndicatorInterface
{

    /**
     * Имя секции в конфиги приложения
     *
     * @var string
     */
    const CONFIG_KEY = 'workflow_zf2_toolkit';

    /**
     * @return array
     */
    public function getModuleDependencies()
    {
        return [
            'OldTown\\Workflow\\ZF2',
            'OldTown\\Workflow\\ZF2\\Service',
            'OldTown\\Workflow\\ZF2\\Dispatch',
            'OldTown\\Workflow\\Doctrine\\ZF2'
        ];
    }

    /**
     * @param EventInterface $e
     *
     * @return array|void
     *
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function onBootstrap(EventInterface $e)
    {
        /** @var MvcEvent $e */
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $sm = $e->getApplication()->getServiceManager();

        /** @var EntryIdResolver $entryIdResolver */
        $entryIdResolver = $sm->get(EntryIdResolver::class);
        $eventManager->attach($entryIdResolver);
    }


    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/',
                ),
            ),
        );
    }

} 