<?php
/**
 * @link    https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\EntryToObjects;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class EntryToObjectsControllerPluginFactory
 *
 * @package OldTown\Workflow\ZF2\Toolkit\EntryToObjects
 */
class EntryToObjectsControllerPluginFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed|void
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $appServiceLocator = $serviceLocator;
        if ($serviceLocator instanceof AbstractPluginManager) {
            $appServiceLocator = $serviceLocator->getServiceLocator();
        }


        /** @var EntryToObjectsService $entryToObjectsService */
        $entryToObjectsService = $appServiceLocator->get(EntryToObjectsService::class);

        $options = [
            'entryToObjectsService' => $entryToObjectsService
        ];

        return new EntryToObjectsControllerPlugin($options);
    }
}
