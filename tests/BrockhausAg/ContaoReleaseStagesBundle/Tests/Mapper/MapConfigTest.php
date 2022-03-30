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

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\MapConfig;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\ArrayOfDNSRecords;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecord;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;
use Contao\TestCase\ContaoTestCase;

/**
 * Class PluginTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper
 */
class MapConfigTest extends ContaoTestCase
{
    /**
     * Test Contao manager plugin class instantiation
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(MapConfig::class, new MapConfig());
    }

    public function testMap(): void
    {
        $input = '{
              "database": {
                "server": "192.168.0.2",
                "name": "prodContao",
                "port": 3306,
                "username": "prodContao",
                "password": "admin1234",
                "ignoredTables": [
                  "tl_to_be_ignored",
                  "tl_to_be_ignored_too"
                ],
                "testStageDatabaseName": "testContao"
              },
              "copyTo": "fileServer",
              "fileServer": {
                "server": "192.168.178.23",
                "port": 1234,
                "username": "admin",
                "password": "admin1234",
                "ssl_tsl": false,
                "path": "test"
              },
              "local": {
                "contaoProdPath": "test"
              },
              "dnsRecords": [
                {
                  "alias": "example-site",
                  "dns": "www.example-site.de"
                },
                {
                  "alias": "example-site-better",
                  "dns": "www.example-site-better.de"
                }
              ],
              "fileFormats": [
                "jpg",
                "mp4",
                "MP4"
              ]
            }
        ';

        $expectedDatabase = new Database("192.168.0.2", "prodContao", 3306, "prodContao",
            "admin1234", array("tl_to_be_ignored", "tl_to_be_ignored_too"), "testContao");
        $expectedFileServer = new FileServer("192.168.178.23", 1234, "admin", "admin1234",
            false, "test");
        $expectedLocal = new Local("test");
        $expectedDNSRecords = new ArrayOfDNSRecords();
        $expectedDNSRecords->add(new DNSRecord("example-site", "www.example-site.de"));
        $expectedDNSRecords->add(new DNSRecord("example-site-better", "www.example-site-better.de"));
        $expected = new Config($expectedDatabase, "fileServer", $expectedFileServer, $expectedLocal,
            $expectedDNSRecords, array("jpg", "mp4", "MP4"));
        $mapper = new MapConfig();

        $actual = $mapper->map(json_decode($input));

        self::assertSame($expectedDatabase->getServer(), $actual->getDatabase()->getServer());
        self::assertSame($expectedDatabase->getName(), $actual->getDatabase()->getName());
        self::assertSame($expectedDatabase->getPort(), $actual->getDatabase()->getPort());
        self::assertSame($expectedDatabase->getUsername(), $actual->getDatabase()->getUsername());
        self::assertSame($expectedDatabase->getPassword(), $actual->getDatabase()->getPassword());
        self::assertSame($expectedDatabase->getIgnoredTables(), $actual->getDatabase()->getIgnoredTables());
        self::assertSame($expectedDatabase->getTestStageDatabaseName(),
            $actual->getDatabase()->getTestStageDatabaseName());
        self::assertSame($expected->getCopyTo(), $actual->getCopyTo());
        self::assertSame($expectedFileServer->getServer(), $actual->getFileServer()->getServer());
        self::assertSame($expectedFileServer->getPort(), $actual->getFileServer()->getPort());
        self::assertSame($expectedFileServer->getUsername(), $actual->getFileServer()->getUsername());
        self::assertSame($expectedFileServer->getPassword(), $actual->getFileServer()->getPassword());
        self::assertSame($expectedFileServer->isSslTsl(), $actual->getFileServer()->isSslTsl());
        self::assertSame($expectedFileServer->getPath(), $actual->getFileServer()->getPath());
        self::assertSame($expectedLocal->getContaoProdPath(), $actual->getLocal()->getContaoProdPath());
        for ($x = 0; $x != $expectedDNSRecords->getLength(); $x++) {
            self::assertSame($expectedDNSRecords->getByIndex($x)->getAlias(),
                $actual->getDnsRecords()->getByIndex($x)->getAlias());
            self::assertSame($expectedDNSRecords->getByIndex($x)->getDns(),
                $actual->getDnsRecords()->getByIndex($x)->getDns());
        }
        self::assertSame($expected->getFileFormats(), $actual->getFileFormats());
    }
}
