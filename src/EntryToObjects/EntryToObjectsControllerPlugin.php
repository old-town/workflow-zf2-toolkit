<?php
/**
 * @link    https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\EntryToObjects;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class EntryToObjectsControllerPlugin
 *
 * @package OldTown\Workflow\ZF2\Toolkit\EntryToObjects
 */
class EntryToObjectsControllerPlugin extends AbstractPlugin
{
    /**
     * Сервис реализующий функционал, для привязки процессов wf и информации о объектах
     *
     * @var EntryToObjectsService
     */
    protected $entryToObjectsService;

    /**
     * EntryToObjectsControllerPlugin constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $initOptions = [
            array_key_exists('entryToObjectsService', $options) ? $options['entryToObjectsService'] : null
        ];

        call_user_func_array([$this, 'init'], $initOptions);
    }

    /**
     * @param EntryToObjectsService $entryToObjectsService
     */
    protected function init(EntryToObjectsService $entryToObjectsService)
    {
        $this->setEntryToObjectsService($entryToObjectsService);
    }

    /**
     * Сервис реализующий функционал, для привязки процессов wf и информации о объектах
     *
     * @return EntryToObjectsService
     */
    public function getEntryToObjectsService()
    {
        return $this->entryToObjectsService;
    }

    /**
     * Устанавливает сервис реализующий функционал, для привязки процессов wf и информации о объектах
     *
     * @param EntryToObjectsService $entryToObjectsService
     *
     * @return $this
     */
    public function setEntryToObjectsService(EntryToObjectsService $entryToObjectsService)
    {
        $this->entryToObjectsService = $entryToObjectsService;

        return $this;
    }

    /**
     * @return EntryToObjectsService
     */
    public function __invoke()
    {
        return $this->getEntryToObjectsService();
    }
}
