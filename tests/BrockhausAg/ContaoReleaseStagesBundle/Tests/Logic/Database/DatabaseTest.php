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

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\Logic\Database;

use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use ReflectionClass;
use ReflectionException;

/**
 * Class IOTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Logic\
 */
class DatabaseTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        $databaseLogicMock = new Database(self::createMock(Connection::class),
            self::createMock(Config::class));
        self::assertInstanceOf(Database::class, $databaseLogicMock);
    }

    /**
     * @throws ReflectionException
     */
    public function testFilterTables(): void
    {
        $tables = array(array("TABLE_NAME" => "test1"), array("TABLE_NAME" => "test2"), array("TABLE_NAME" => "a"),
            array("TABLE_NAME" => "b"));
        $ignoredTables = array("a", "test1");

        $expected = array("test2", "b");

        $databaseLogic = self::createMock(Database::class);
        $reflection = new ReflectionClass($databaseLogic);
        $reflectionMethod = $reflection->getMethod("filterTables");
        $reflectionMethod->setAccessible(true);
        $actual = $reflectionMethod->invokeArgs($databaseLogic, array($tables, $ignoredTables));

        for ($x = 0; $x != count($expected); $x++)
        {
            self::assertSame($expected[$x], $actual[$x]);
        }
    }
}
