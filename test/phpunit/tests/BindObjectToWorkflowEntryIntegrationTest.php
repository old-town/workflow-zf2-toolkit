<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\PhpUnit\Test;

use Doctrine\ORM\Tools\SchemaTool;
use OldTown\Workflow\ZF2\Dispatch\Dispatcher\Dispatcher;
use OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\TestPaths;
use Zend\Mvc\Application;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OldTown\Workflow\ZF2\Dispatch\Dispatcher\WorkflowDispatchEvent;
use OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\BindObjectToWorkflowEntryIntegrationTest\Entity\TestEntity;

/**
 * Class BindObjectToWorkflowEntryIntegrationTest
 *
 * @package OldTown\Workflow\ZF2\Toolkit\PhpUnit\Test
 */
class BindObjectToWorkflowEntryIntegrationTest extends AbstractHttpControllerTestCase
{
    /**
     * Подготавливаем базу
     *
     */
    protected function setUp()
    {
        /** @noinspection PhpIncludeInspection */
        $this->setApplicationConfig(
            include TestPaths::getPathToBindObjectToWorkflowEntryIntegrationTest()
        );
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getApplication()->getServiceManager()->get('doctrine.entitymanager.test');

        $tool = new SchemaTool($em);
        $tool->dropDatabase();

        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool->createSchema($metadata);

        parent::setUp();
    }


    /**
     * Проверка работы сервиса привязыающего объект к процессу wf, и востанавливающий его.
     *
     * Для тестирования используется простое workflow состоящее из одного шага.
     * При выполнении initAction происходит привязка процесса wf к объекту. Далее у единственного объявленного шага
     * вызывается переход на самого себя. В рамках этого перехода происходит востановление привязанного объекта
     *
     * При тестирование используется тестовый контроллер с двумя action. Один из которых отвечает за вызов initAction у
     * wf, а второй за вызов doAction.
     *
     * При вызове действия контроллера отвечающего за иницииирование wf, в качестве параметра передается тестовое значение.
     * Это тестове значение записывается в свойство value \OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\BindObjectToWorkflowEntryIntegrationTest\Entity\TestEntity.
     *
     * Далее вызывается doAction для уже созданного процесса wf. В рамках этого перехода, ожидаем что будет произведено
     * востановление объекта.
     *
     * В случае успешной работы, сверяем ожидаемое значение, с значение извлеченным из востановленного объекта
     *
     * @return void
     */
    public function testBindObjectToWorkflowEntry()
    {
        /** @noinspection PhpIncludeInspection */
        $this->setApplicationConfig(
            include TestPaths::getPathToBindObjectToWorkflowEntryIntegrationTest()
        );

        /** @var Application $app */
        $app = $this->getApplication();

        $expectedValue = 'test_completed';
        $url = sprintf('/level1/initialize/%s', $expectedValue);
        $this->dispatch($url);

        /** @var WorkflowDispatchEvent $dispatchEvent */
        $dispatchEvent = $app->getMvcEvent()->getParam(Dispatcher::WORKFLOW_DISPATCH_EVENT);
        $transientVars = $dispatchEvent->getWorkflowResult()->getTransientVars();

        /** @var TestEntity $testObject */
        $testObject = $transientVars['testObject'];

        $url = sprintf('/level1/doAction/%s', $testObject->getId());
        $this->dispatch($url);

        /** @var WorkflowDispatchEvent $dispatchEvent */
        $dispatchEvent = $app->getMvcEvent()->getParam(Dispatcher::WORKFLOW_DISPATCH_EVENT);
        $transientVars = $dispatchEvent->getWorkflowResult()->getTransientVars();

        /** @var TestEntity $testObject */
        $testObject = $transientVars['actualResult'];

        $actualValue = $testObject->getValue();

        static::assertEquals($expectedValue, $actualValue);
    }
}
