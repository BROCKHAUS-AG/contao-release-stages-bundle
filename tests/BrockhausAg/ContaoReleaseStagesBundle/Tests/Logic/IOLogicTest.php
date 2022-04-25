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

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IOLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecordCollection;
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
        self::assertInstanceOf(IO::class, new IO("", $systemConfigMock,
            self::createMock(Log::class)));
    }


    public function testGetContaoPath(): void
    {
        $expected = "test/files";
        $systemConfigMock = self::createMock(SystemConfig::class);
        $ioLogic = new IO("test", $systemConfigMock, self::createMock(Log::class));

        $actual = $ioLogic->getPathToContaoFiles();

        self::assertSame($expected, $actual);
    }

    public function testGetDatabaseConfiguration(): void
    {
        $expected = new Database("server", "name", 0, "username", "password", array(),
            "testStageDatabaseName");
        $willReturn = new Config($expected, "", self::createMock(FileServer::class),
            self::createMock(Local::class),
            self::createMock(DNSRecordCollection::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getDatabaseConfiguration();

        self::assertSame($expected->getServer(), $actual->getServer());
        self::assertSame($expected->getName(), $actual->getName());
        self::assertSame($expected->getPort(), $actual->getPort());
        self::assertSame($expected->getUsername(), $actual->getUsername());
        self::assertSame($expected->getPassword(), $actual->getPassword());
        self::assertSame($expected->getIgnoredTables(), $actual->getIgnoredTables());
    }

    public function testGetDatabaseIgnoredTablesConfiguration(): void
    {
        $database = new Database("server", "name", 0, "username", "password",
            array("a", "b"), "testStageDatabaseName");
        $willReturn = new Config($database, "", self::createMock(FileServer::class),
            self::createMock(Local::class),
            self::createMock(DNSRecordCollection::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);
        $expected = $database->getIgnoredTables();
        array_push($expected, "tl_user", "tl_cron_job", "tl_release_stages");

        $actual = $ioLogic->getDatabaseIgnoredTablesConfiguration();

        for ($x = 0; $x != sizeof($expected); $x++) {
            self::assertSame($expected[$x], $actual[$x]);
        }
    }

    public function testGetDNSRecords(): void
    {
        $expected = new DNSRecordCollection();
        $expected->add(new DNSRecord("a", "b"));
        $expected->add(new DNSRecord("c", "d"));
        $willReturn = new Config(self::createMock(Database::class), "",
            self::createMock(FileServer::class), self::createMock(Local::class),
            $expected, array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getDNSRecords();

        for ($x = 0; $x != $expected->getLength(); $x++) {
            self::assertSame($expected->getByIndex($x)->getDns(), $actual->getByIndex($x)->getDns());
            self::assertSame($expected->getByIndex($x)->getAlias(), $actual->getByIndex($x)->getAlias());
        }
    }

    public function testGetWhereToCopy(): void
    {
        $expected = "test";
        $willReturn = new Config(self::createMock(Database::class), $expected,
            self::createMock(FileServer::class), self::createMock(Local::class),
            self::createMock(DNSRecordCollection::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getWhereToCopy();

        self::assertSame($expected, $actual);
    }

    public function testGetFileServerConfiguration(): void
    {
        $expected = new FileServer("server", 0, "username", "password", true,
            "path");
        $willReturn = new Config(self::createMock(Database::class), "", $expected,
            self::createMock(Local::class), self::createMock(DNSRecordCollection::class),
            array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getFileServerConfiguration();

        self::assertSame($expected->getServer(), $actual->getServer());
        self::assertSame($expected->getPort(), $actual->getPort());
        self::assertSame($expected->getUsername(), $actual->getUsername());
        self::assertSame($expected->getPassword(), $actual->getPassword());
        self::assertSame($expected->isSslTsl(), $actual->isSslTsl());
        self::assertSame($expected->getPath(), $actual->getPath());
    }

    public function testGetLocalServerConfiguration(): void
    {
        $expected = new Local("contaoProdPath");
        $willReturn = new Config(self::createMock(Database::class), "",
            self::createMock(FileServer::class), $expected,
            self::createMock(DNSRecordCollection::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getLocalFileServerConfiguration();

        self::assertSame($expected->getContaoProdPath(), $actual->getContaoProdPath());
    }

    public function testGetFileFormats(): void
    {
        $expected = array("a", "b", "c");
        $willReturn = new Config(self::createMock(Database::class), "",
            $this->createMock(FileServer::class), self::createMock(Local::class),
            self::createMock(DNSRecordCollection::class), $expected);
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getFileFormats();

        for ($x = 0; $x != count($actual); $x++) {
            self::assertSame($expected[$x], $actual[$x]);
        }
    }

    private function createIOLogicInstanceWithConfigMock(Config $willReturn) : IO
    {
        $systemConfigMock = self::createMock(SystemConfig::class);
        $systemConfigMock->method("getConfig")->willReturn($willReturn);
        return new IO("test", $systemConfigMock, self::createMock(Log::class));
    }
}
