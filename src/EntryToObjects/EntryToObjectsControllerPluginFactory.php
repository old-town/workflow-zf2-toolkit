<?php
/**
 * @link    https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\EntryToObjects;

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
        /** @var EntryToObjectsService $entryToObjectsService */
        $entryToObjectsService = $serviceLocator->get(EntryToObjectsService::class);

        $options = [
            'entryToObjectsService' => $entryToObjectsService
        ];

        return new EntryToObjectsControllerPlugin($options);
    }
}
