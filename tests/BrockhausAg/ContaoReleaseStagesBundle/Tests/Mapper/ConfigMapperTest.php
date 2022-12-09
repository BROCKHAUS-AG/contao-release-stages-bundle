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

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\ConfigMapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecordCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecord;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ftp;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ssh;
use Contao\TestCase\ContaoTestCase;

/**
 * Class ConfigMapperTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper
 */
class ConfigMapperTest extends ContaoTestCase
{
    /**
     * Test Contao manager plugin class instantiation
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(ConfigMapper::class, new ConfigMapper());
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
                ]
              },
              "fileServer": {
                "server": "192.168.178.23",
                "path": "test",
                "ftp": {
                    "port": 1234,
                    "username": "admin",
                    "password": "admin1234",
                    "ssl": false
                },
                "ssh": {
                    "port": 1234,
                    "username": "admin",
                    "password": "admin1234"
                }
              },
              "maxSpendTimeWhileCreatingRelease": 0,
              "dnsRecords": [
                {
                  "alias": "example-site",
                  "dns": "www.example-site.de"
                },
                {
                  "alias": "example-site-better",
                  "dns": "www.example-site-better.de"
                }
              ]
            }
        ';

        $expectedDatabase = new Database("192.168.0.2", "prodContao", 3306, "prodContao",
            "admin1234", array("tl_to_be_ignored", "tl_to_be_ignored_too"));
        $expectedFtp = new Ftp(1234, "admin", "admin1234", false);
        $expectedSsh = new Ssh(1234, "admin", "admin1234");
        $expectedFileServer = new FileServer("192.168.178.23", "test", $expectedFtp, $expectedSsh);
        $expectedDNSRecords = new DNSRecordCollection();
        $expectedDNSRecords->add(new DNSRecord("example-site", "www.example-site.de"));
        $expectedDNSRecords->add(new DNSRecord("example-site-better", "www.example-site-better.de"));
        $expected = new Config($expectedDatabase, $expectedFileServer, 0,
            $expectedDNSRecords);
        $mapper = new ConfigMapper();

        $actual = $mapper->map(json_decode($input));

        self::assertSame($expectedDatabase->getServer(), $actual->getDatabase()->getServer());
        self::assertSame($expectedDatabase->getName(), $actual->getDatabase()->getName());
        self::assertSame($expectedDatabase->getPort(), $actual->getDatabase()->getPort());
        self::assertSame($expectedDatabase->getUsername(), $actual->getDatabase()->getUsername());
        self::assertSame($expectedDatabase->getPassword(), $actual->getDatabase()->getPassword());
        self::assertSame($expectedDatabase->getIgnoredTables(), $actual->getDatabase()->getIgnoredTables());
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
        self::assertSame($expected->getMaxSpendTimeWhileCreatingRelease(), $actual->getMaxSpendTimeWhileCreatingRelease());
    }
}
