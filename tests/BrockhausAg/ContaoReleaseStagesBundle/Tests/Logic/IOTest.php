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

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecordCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecord;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ftp;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ssh;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemConfig;
use Contao\TestCase\ContaoTestCase;

/**
 * Class IOTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Logic\
 */
class IOTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        $systemConfigMock = self::createMock(SystemConfig::class);
        self::assertInstanceOf(IO::class, new IO("", $systemConfigMock,
            self::createMock(Logger::class)));
    }


    public function testGetContaoPath(): void
    {
        $expected = "test/files";
        $systemConfigMock = self::createMock(SystemConfig::class);
        $ioLogic = new IO("test", $systemConfigMock, self::createMock(Logger::class));

        $actual = $ioLogic->getPathToContaoFiles();

        self::assertSame($expected, $actual);
    }

    public function testGetDatabaseConfiguration(): void
    {
        $expected = new Database("server", "name", 0, "username", "password", array());
        $willReturn = new Config($expected, "", self::createMock(FileServer::class),
            self::createMock(Local::class), 0,
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
            array("a", "b"));
        $willReturn = new Config($database, "", self::createMock(FileServer::class),
            self::createMock(Local::class), 0,
            self::createMock(DNSRecordCollection::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);
        $expected = $database->getIgnoredTables();
        array_push($expected, "tl_user", "tl_cron_job", "tl_deployments");

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
            0, $expected, array());
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
            0, self::createMock(DNSRecordCollection::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getWhereToCopy();

        self::assertSame($expected, $actual);
    }

    public function testGetFTPConfiguration(): void
    {
        $expected = new Ftp(0, "username", "password", true);
        $fileServer = new FileServer("server", "path", $expected, self::createMock(Ssh::class));
        $willReturn = new Config(self::createMock(Database::class), "", $fileServer,
            self::createMock(Local::class), 0,
            self::createMock(DNSRecordCollection::class), array());

        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getFTPConfiguration();

        self::assertSame($expected->getPassword(), $actual->getPassword());
        self::assertSame($expected->getUsername(), $actual->getUsername());
        self::assertSame($expected->getPort(), $actual->getPort());
        self::assertSame($expected->isSsl(), $actual->isSsl());
    }

    public function testGetSSHConfiguration(): void
    {
        $expected = new Ssh(0, "username", "password");
        $fileServer = new FileServer("server", "path", self::createMock(Ftp::class), $expected);
        $willReturn = new Config(self::createMock(Database::class), "", $fileServer,
            self::createMock(Local::class), 0,
            self::createMock(DNSRecordCollection::class), array());

        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getSSHConfiguration();

        self::assertSame($expected->getPassword(), $actual->getPassword());
        self::assertSame($expected->getUsername(), $actual->getUsername());
        self::assertSame($expected->getPort(), $actual->getPort());
    }

    public function testGetFileServerConfiguration(): void
    {
        $expected_ftp = new Ftp(0, "username", "password", true);
        $expected_ssh = new Ssh(0, "username", "password");
        $expected = new FileServer("server", "path", $expected_ftp, $expected_ssh);
        $willReturn = new Config(self::createMock(Database::class), "", $expected,
            self::createMock(Local::class), 0,
            self::createMock(DNSRecordCollection::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getFileServerConfiguration();

        self::assertSame($expected->getServer(), $actual->getServer());
        self::assertSame($expected->getPath(), $actual->getPath());

        self::assertSame($expected->getFtp()->getPort(), $actual->getFtp()->getPort());
        self::assertSame($expected->getFtp()->getUsername(), $actual->getFtp()->getUsername());
        self::assertSame($expected->getFtp()->getPassword(), $actual->getFtp()->getPassword());
        self::assertSame($expected->getFtp()->isSsl(), $actual->getFtp()->isSsl());

        self::assertSame($expected->getSsh()->getPort(), $actual->getSsh()->getPort());
        self::assertSame($expected->getSsh()->getUsername(), $actual->getSsh()->getUsername());
        self::assertSame($expected->getSsh()->getPassword(), $actual->getSsh()->getPassword());
    }

    public function testGetLocalServerConfiguration(): void
    {
        $expected = new Local("contaoProdPath");
        $willReturn = new Config(self::createMock(Database::class), "",
            self::createMock(FileServer::class), $expected, 0,
            self::createMock(DNSRecordCollection::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getLocalFileServerConfiguration();

        self::assertSame($expected->getContaoProdPath(), $actual->getContaoProdPath());
    }

    public function testGetMaxSpendTimeWhileCreatingRelease(): void
    {
        $expected = 0;
        $willReturn = new Config(self::createMock(Database::class), "",
            self::createMock(FileServer::class), self::createMock(Local::class),
            0, self::createMock(DNSRecordCollection::class), array());
        $ioLogic = $this->createIOLogicInstanceWithConfigMock($willReturn);

        $actual = $ioLogic->getMaxSpendTimeWhileCreatingRelease();

        self::assertSame($expected, $actual);
    }

    public function testGetFileFormats(): void
    {
        $expected = array("a", "b", "c");
        $willReturn = new Config(self::createMock(Database::class), "",
            $this->createMock(FileServer::class), self::createMock(Local::class),
            0, self::createMock(DNSRecordCollection::class), $expected);
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
        return new IO("test", $systemConfigMock, self::createMock(Logger::class));
    }
}
