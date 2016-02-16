<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\PhpUnit\Utils;

use Exception;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

/**
 * Class InitTestAppListener
 *
 * @package OldTown\Workflow\ZF2\Toolkit\PhpUnit\Utils
 */
class InitTestAppListener extends AbstractListenerAggregate implements \PHPUnit_Framework_TestListener
{
    /**
     * @var string
     */
    protected static $connectionName;

    /**
     * @var string
     */
    protected static $driverClass;

    /**
     * @var array
     */
    protected static $params;

    /**
     * Флаг определяющий был ли установленны данные при запуске phpunit
     *
     * @var bool
     */
    protected static $flagInitData = false;

    /**
     * @inheritDoc
     */
    public function __construct($connectionName = null, $driverClass = null, array $params = [])
    {
        if (!static::$flagInitData) {
            static::$connectionName = $connectionName;
            static::$driverClass = $driverClass;
            static::$params = $params;

            static::$flagInitData = true;
        }
    }

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return static::$connectionName;
    }

    /**
     * @return string
     */
    public function getDriverClass()
    {
        return static::$driverClass;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return static::$params;
    }

    /**
     * Подписываемся на событие приложения
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(MvcEvent::EVENT_BOOTSTRAP, [$this, 'onBootstrap']);
    }

    /**
     * @param MvcEvent $e
     * @throws \Zend\ServiceManager\ServiceLocatorInterface
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     * @throws \Zend\ServiceManager\Exception\InvalidServiceNameException
     */
    public function onBootstrap(MvcEvent $e)
    {
        $connectionName = $this->getConnectionName();
        $driverClass = $this->getDriverClass();
        $params = $this->getParams();

        /** @var ServiceManager $sm */
        $sm = $e->getApplication()->getServiceManager();
        $appConfig = $sm->get('Config');

        $data = [
            'doctrine' => [
                'connection' => [
                    $connectionName => [
                        'driverClass' => $driverClass,
                        'params' => $params
                    ]
                ]
            ]
        ];
        $newAppConfig = ArrayUtils::merge($appConfig, $data);

        $originalAllowOverride = $sm->getAllowOverride();
        $sm->setAllowOverride(true);
        $sm->setService('config', $newAppConfig);
        $sm->setAllowOverride($originalAllowOverride);
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
    }
}
