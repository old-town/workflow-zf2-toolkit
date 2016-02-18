<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\BindObjectToWorkflowEntryIntegrationTest;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use OldTown\Workflow\ZF2\Dispatch\Annotation as WFD;
use OldTown\Workflow\ZF2\Dispatch\Dispatcher\Dispatcher;
use OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\BindObjectToWorkflowEntryIntegrationTest\Entity\TestEntity;
use Doctrine\ORM\EntityManager;


/**
 * Class TestController
 *
 * @package OldTown\Workflow\ZF2\Dispatch\PhpUnit\TestData\IntegrationTest
 */
class TestController extends AbstractActionController
{

    /**
     * Подготовка данных для workflow
     *
     * @return array
     *
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     */
    public function prepareWorkflowDataHandler()
    {
        $expectedValue = $this->getEvent()->getRouteMatch()->getParam('expectedValue');


        $testEntity = new TestEntity();
        $testEntity->setValue($expectedValue);

        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('doctrine.entitymanager.test');

        $em->persist($testEntity);
        $em->flush();

        return [
            'testObject' => $testEntity
        ];
    }

    /**
     * Условие для запуска workflow
     */
    public function testCondition()
    {
        return true;
    }


    /**
     * @WFD\WorkflowDispatch(enabled=true, activity="initialize")
     * @WFD\PrepareData(type="method", handler="prepareWorkflowDataHandler", enabled=true)
     */
    public function initializeAction()
    {
        $this->getEvent()->getParam(Dispatcher::WORKFLOW_DISPATCH_EVENT);

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    /**
     * @WFD\WorkflowDispatch(enabled=true, activity="doAction")
     */
    public function doAction()
    {
        $this->getEvent()->getParam(Dispatcher::WORKFLOW_DISPATCH_EVENT);

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}
