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

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\Logic;

use BrockhausAg\ContaoReleaseStagesBundle\Logic\IOLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\ArrayOfDNSRecords;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecord;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemConfig;
use Contao\TestCase\ContaoTestCase;

/**
 * Class IOLogicTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Logic\
 */
class IOLogicTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        $systemConfigMock = self::createMock(SystemConfig::class);
        self::assertInstanceOf(IOLogic::class, new IOLogic("", $systemConfigMock));
    }


    public function testLoadContaoPath(): void
    {
        $expected = "test/files";
        $systemConfigMock = self::createMock(SystemConfig::class);
        $ioLogic = new IOLogic("test", $systemConfigMock);

        $actual = $ioLogic->loadPathToContaoFiles();

        self::assertSame($expected, $actual);
    }

    public function testLoadDatabaseConfiguration(): void
    {
        $expected = new Database("server", "name", 0, "username", "password", array(),
            "testStageDatabaseName");
        $willReturn = new Config($expected, "", self::createMock(FileServer::class),
            self::createMock(Local::class),
            self::createMock(ArrayOfDNSRecords::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->loadDatabaseConfiguration();

        self::assertSame($expected->getServer(), $actual->getServer());
        self::assertSame($expected->getName(), $actual->getName());
        self::assertSame($expected->getPort(), $actual->getPort());
        self::assertSame($expected->getUsername(), $actual->getUsername());
        self::assertSame($expected->getPassword(), $actual->getPassword());
        self::assertSame($expected->getIgnoredTables(), $actual->getIgnoredTables());
        self::assertSame($expected->getTestStageDatabaseName(), $actual->getTestStageDatabaseName());
    }

    public function testLoadTestStageDatabaseName(): void
    {
        $database = new Database("server", "name", 0, "username", "password", array(),
            "testStageDatabaseName");
        $willReturn = new Config($database, "", self::createMock(FileServer::class),
            self::createMock(Local::class),
            self::createMock(ArrayOfDNSRecords::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);
        $expected = $database->getTestStageDatabaseName();

        $actual = $ioLogic->loadTestStageDatabaseName();

        self::assertSame($expected, $actual);
    }

    public function testLoadDatabaseIgnoredTablesConfiguration(): void
    {
        $database = new Database("server", "name", 0, "username", "password",
            array("a", "b"), "testStageDatabaseName");
        $willReturn = new Config($database, "", self::createMock(FileServer::class),
            self::createMock(Local::class),
            self::createMock(ArrayOfDNSRecords::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);
        $expected = $database->getIgnoredTables();
        array_push($expected, "tl_user", "tl_cron_job", "tl_release_stages");

        $actual = $ioLogic->loadDatabaseIgnoredTablesConfiguration();

        for ($x = 0; $x != sizeof($expected); $x++) {
            self::assertSame($expected[$x], $actual[$x]);
        }
    }

    public function testLoadDNSRecords(): void
    {
        $expected = new ArrayOfDNSRecords();
        $expected->add(new DNSRecord("a", "b"));
        $expected->add(new DNSRecord("c", "d"));
        $willReturn = new Config(self::createMock(Database::class), "",
            self::createMock(FileServer::class), self::createMock(Local::class),
            $expected, array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->loadDNSRecords();

        for ($x = 0; $x != $expected->getLength(); $x++) {
            self::assertSame($expected->getByIndex($x)->getDns(), $actual->getByIndex($x)->getDns());
            self::assertSame($expected->getByIndex($x)->getAlias(), $actual->getByIndex($x)->getAlias());
        }
    }

    public function testCheckWhereToCopy(): void
    {
        $expected = "test";
        $willReturn = new Config(self::createMock(Database::class), $expected,
            self::createMock(FileServer::class), self::createMock(Local::class),
            self::createMock(ArrayOfDNSRecords::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->checkWhereToCopy();

        self::assertSame($expected, $actual);
    }

    private function createIOLogicInstanceWithConfigMock(Config $willReturn) : IOLogic
    {
        $systemConfigMock = self::createMock(SystemConfig::class);
        $systemConfigMock->method("getConfig")->willReturn($willReturn);
        return new IOLogic("test", $systemConfigMock);
    }
}
