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

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\System;


use BrockhausAg\ContaoReleaseStagesBundle\Exception\File\ConfigNotFound;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\ConfigMapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecord;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecordCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ftp;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ssh;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemConfig;
use Contao\TestCase\ContaoTestCase;
use ReflectionClass;

/**
 * Class SystemConfigTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\System
 */
class SystemConfigTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        $systemConfig = self::createMock(SystemConfig::class);
        self::assertInstanceOf(SystemConfig::class, $systemConfig);
    }

    public function testGetConfig_shouldThrowConfigNotFoundException(): void
    {
        self::expectException(ConfigNotFound::class);
        $systemConfig = new SystemConfig("", self::createMock(ConfigMapper::class),
            self::createMock(Logger::class));
        $systemConfig->getConfig();
    }

    /**
     * @throws ConfigNotFound
     */
    public function testGetConfig_shouldReturnConfig(): void
    {
        $systemConfig = new SystemConfig("", self::createMock(ConfigMapper::class),
            self::createMock(Logger::class));
        $expectedProdDatabase = new Database("192.168.0.2", "prodContao", 3306, "prodContao",
            "admin1234", array("tl_to_be_ignored", "tl_to_be_ignored_too"));
        $expectedTestDatabase = new Database("hier-kommen-die-daten-her", "testContao", 3306, "admin",
            "admin1234", array("tl_to_be_ignored", "tl_to_be_ignored_too"));
        $expectedFtp = new Ftp(1234, "admin", "admin1234", false);
        $expectedSsh = new Ssh(1234, "admin", "admin1234");
        $expectedFileServer = new FileServer("192.168.178.23", "test", $expectedFtp, $expectedSsh);
        $expectedDNSRecords = new DNSRecordCollection();
        $expectedDNSRecords->add(new DNSRecord("example-site", "www.example-site.de"));
        $expectedDNSRecords->add(new DNSRecord("example-site-better", "www.example-site-better.de"));
        $expected = new Config($expectedProdDatabase, $expectedTestDatabase, $expectedFileServer, 0,
            $expectedDNSRecords);

        $reflection = new ReflectionClass($systemConfig);
        $reflection_property = $reflection->getProperty("_config");
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($systemConfig, $expected);

        $actual = $systemConfig->getConfig();

        self::assertSame($expectedProdDatabase->getServer(), $actual->getProdDatabase()->getServer());
        self::assertSame($expectedProdDatabase->getName(), $actual->getProdDatabase()->getName());
        self::assertSame($expectedProdDatabase->getPort(), $actual->getProdDatabase()->getPort());
        self::assertSame($expectedProdDatabase->getUsername(), $actual->getProdDatabase()->getUsername());
        self::assertSame($expectedProdDatabase->getPassword(), $actual->getProdDatabase()->getPassword());
        self::assertSame($expectedProdDatabase->getIgnoredTables(), $actual->getProdDatabase()->getIgnoredTables());
        self::assertSame($expectedTestDatabase->getServer(), $actual->getTestDatabase()->getServer());
        self::assertSame($expectedTestDatabase->getName(), $actual->getTestDatabase()->getName());
        self::assertSame($expectedTestDatabase->getPort(), $actual->getTestDatabase()->getPort());
        self::assertSame($expectedTestDatabase->getUsername(), $actual->getTestDatabase()->getUsername());
        self::assertSame($expectedTestDatabase->getPassword(), $actual->getTestDatabase()->getPassword());
        self::assertSame($expectedTestDatabase->getIgnoredTables(), $actual->getTestDatabase()->getIgnoredTables());
        self::assertSame($expectedFileServer->getServer(), $actual->getFileServer()->getServer());
        self::assertSame($expectedFileServer->getRootPath(), $actual->getFileServer()->getRootPath());
        self::assertSame($expectedFtp->getPort(), $actual->getFileServer()->getFtp()->getPort());
        self::assertSame($expectedFtp->getUsername(), $actual->getFileServer()->getFtp()->getUsername());
        self::assertSame($expectedFtp->getPassword(), $actual->getFileServer()->getFtp()->getPassword());
        self::assertSame($expectedFtp->isSsl(), $actual->getFileServer()->getFtp()->isSsl());
        self::assertSame($expectedFtp->getPort(), $actual->getFileServer()->getSsh()->getPort());
        self::assertSame($expectedFtp->getUsername(), $actual->getFileServer()->getSsh()->getUsername());
        self::assertSame($expectedFtp->getPassword(), $actual->getFileServer()->getSsh()->getPassword());
        for ($x = 0; $x != $expectedDNSRecords->getLength(); $x++) {
            self::assertSame($expectedDNSRecords->getByIndex($x)->getAlias(),
                $actual->getDnsRecords()->getByIndex($x)->getAlias());
            self::assertSame($expectedDNSRecords->getByIndex($x)->getDns(),
                $actual->getDnsRecords()->getByIndex($x)->getDns());
        }
    }
}
