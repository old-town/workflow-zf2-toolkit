<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\PhpUnit\Test;

use Doctrine\ORM\Tools\SchemaTool;
use OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData\TestPaths;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

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
     *
     * @return void
     */
    public function testLoadModule()
    {
        /** @noinspection PhpIncludeInspection */
        $this->setApplicationConfig(
            include TestPaths::getPathToBindObjectToWorkflowEntryIntegrationTest()
        );

        $this->assertModulesLoaded(['OldTown\\Workflow\\ZF2\\Toolkit']);
    }
}
