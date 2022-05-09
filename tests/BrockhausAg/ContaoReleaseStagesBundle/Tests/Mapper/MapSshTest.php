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

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\MapSsh;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ssh;
use Contao\TestCase\ContaoTestCase;

/**
 * Class MapSshTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper
 */
class MapSshTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        self::assertInstanceOf(MapSsh::class, new MapSsh());
    }

    public function testMap() : void
    {
        $input = '{
            "port": 1234,
            "username": "admin",
            "password": "admin1234"
        }';

        $expected = new Ssh(1234, "admin", "admin1234");

        $mapper = new MapSsh();
        $actual = $mapper->map(json_decode($input));

        self::assertSame($expected->getPort(), $actual->getPort());
        self::assertSame($expected->getUsername(), $actual->getUsername());
        self::assertSame($expected->getPassword(), $actual->getPassword());
    }
}
