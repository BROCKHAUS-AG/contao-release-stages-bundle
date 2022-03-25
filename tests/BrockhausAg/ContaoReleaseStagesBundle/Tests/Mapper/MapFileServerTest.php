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
use Contao\TestCase\ContaoTestCase;

/**
 * Class MapFileServerTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper
 */
class MapFileServerTest extends ContaoTestCase
{
    /**
     * Test Contao manager plugin class instantiation
     */
    public function testInstantiation(): void
    {
        self::assertInstanceOf(MapFileServer::class, new MapFileServer());
    }

    public function testMap() : void
    {
        $input = '{
            "server": "192.168.178.23",
            "port": 1234,
            "username": "admin",
            "password": "admin1234",
            "ssl_tsl": false,
            "path": "test"
          }';
        $expected = new FileServer("192.168.178.23", 1234, "admin", "admin1234",
            false, "test");
        $mapper = new MapFileServer();

        $actual = $mapper->map(json_decode($input));

        self::assertSame($expected->getServer(), $actual->getServer());
        self::assertSame($expected->getPort(), $actual->getPort());
        self::assertSame($expected->getUsername(), $actual->getUsername());
        self::assertSame($expected->getPassword(), $actual->getPassword());
        self::assertSame($expected->isSslTsl(), $actual->isSslTsl());
        self::assertSame($expected->getPath(), $actual->getPath());
    }
}
