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

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\MapFileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ftp;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ssh;
use Contao\TestCase\ContaoTestCase;

/**
 * Class MapFileServerTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper
 */
class MapFileServerTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        self::assertInstanceOf(MapFileServer::class, new MapFileServer());
    }

    public function testMap() : void
    {
        $input = '{
            "server": "192.168.178.23",
            "path": "test",
            "ftp": {
                "port": 1234,
                "username": "admin",
                "password": "admin1234",
                "ssl": true
            },
            "ssh": {
                "port": 1234,
                "username": "admin",
                "password": "admin1234"
            }
          }';

        $expectedFtp = new Ftp(1234, "admin", "admin1234", true);
        $expectedSsh = new Ssh(1234, "admin", "admin1234");
        $expected = new FileServer("192.168.178.23", "test", $expectedFtp, $expectedSsh);

        $mapper = new MapFileServer();
        $actual = $mapper->map(json_decode($input));

        self::assertSame($expected->getServer(), $actual->getServer());
        self::assertSame($expected->getPath(), $actual->getPath());
        self::assertSame($expectedFtp->getPort(), $actual->getFtp()->getPort());
        self::assertSame($expectedFtp->getUsername(), $actual->getFtp()->getUsername());
        self::assertSame($expectedFtp->getPassword(), $actual->getFtp()->getPassword());
        self::assertSame($expectedFtp->isSsl(), $actual->getFtp()->isSsl());
        self::assertSame($expectedSsh->getPort(), $actual->getSsh()->getPort());
        self::assertSame($expectedSsh->getUsername(), $actual->getSsh()->getUsername());
        self::assertSame($expectedSsh->getPassword(), $actual->getSsh()->getPassword());
    }
}
