<?php

/*
 * This file is part of contao-release-stages-bundle.
 *
 * (c) BROCKHAUS AG 2022 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-release-stages-bundle
 */
declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\Logic\FileServer;

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\LocalLoader;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use BrockhausAg\ContaoReleaseStagesBundle\Model\FileCollection;
use Contao\TestCase\ContaoTestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Class LocalLoaderTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\
 */
class LocalLoaderTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        $loadFromLocalMock = self::createMock(LocalLoader::class);
        self::assertInstanceOf(LocalLoader::class, $loadFromLocalMock);
    }

    /**
     * @throws ReflectionException
     */
    public function testChangePathToProdPath(): void
    {
        $expected = "test/prodPath/hello";
        $input = "test/path/hello";

        $class = new ReflectionClass(LocalLoader::class);
        $method = $class->getMethod("changePathToProdPath");
        $method->setAccessible(true);
        $loadFromLocalMock = new LocalLoader(self::createMock(IO::class),
            self::createMock(Log::class), "path", "prodPath");

        $actual = $method->invokeArgs($loadFromLocalMock, [$input]);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws ReflectionException
     */
    public function testLoadFiles(): void
    {
        $files = array("path/test/a.a", "path/test/a.b");
        $expected = new FileCollection();
        $expected->add(new File($files[0], "prodPath/test/a.a"));
        $expected->add(new File($files[1], "prodPath/test/a.b"));

        $loadFromLocalMock = self::createMock(LocalLoader::class);
        $reflection = new ReflectionClass($loadFromLocalMock);
        $reflection_property = $reflection->getProperty("_path");
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($loadFromLocalMock, "path");
        $reflection_property = $reflection->getProperty("_prodPath");
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($loadFromLocalMock, "prodPath");
        $class = new ReflectionClass(LocalLoader::class);
        $method = $class->getMethod("loadFiles");
        $method->setAccessible(true);

        $loadFromLocalMock->method("getTimestampFromFile")->willReturn(0);

        $actual = $method->invoke($loadFromLocalMock, $files);

        for ($x = 0; $x != count($expected->get()); $x++) {
            self::assertSame($expected->getByIndex($x)->getPath(), $actual->getByIndex($x)->getPath());
            self::assertSame($expected->getByIndex($x)->getProdPath(), $actual->getByIndex($x)->getProdPath());
        }
    }
}
