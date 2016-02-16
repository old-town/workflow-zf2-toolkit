<?php
/**
 * @link     https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData;

/**
 * Class TestPaths
 *
 * @package OldTown\Workflow\ZF2\Toolkit\PhpUnit\TestData
 */
class TestPaths
{
    /**
     * Путь до директории где находится файл инициирующий приложение
     *
     * @return string
     */
    public static function getPathToModule()
    {
        return __DIR__ . '/../../../';
    }


    /**
     * Возвращает путь путь до директории в которой создаются прокси классы для сущностей доктрины
     *
     * @return string
     */
    public static function getPathToDoctrineProxyDir()
    {
        return __DIR__ . '/../../../data/test/Proxies/';
    }

    /**
     * Путь до конфига приложения по умолчанию
     */
    public static function getPathToDefaultAppConfig()
    {
        return  __DIR__ . '/../_files/DefaultApp/application.config.php';
    }
}
