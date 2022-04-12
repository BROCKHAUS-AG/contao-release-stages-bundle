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

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\MapDatabase;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use Contao\TestCase\ContaoTestCase;

/**
 * Class MapDatabaseTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper
 */
class MapDatabaseTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        self::assertInstanceOf(MapDatabase::class, new MapDatabase());
    }

    public function testMap(): void
    {
        $input = '{
            "server": "192.168.0.2",
            "name": "prodContao",
            "port": 3306,
            "username": "prodContao",
            "password": "admin1234",
            "ignoredTables": [
                "tl_to_be_ignored",
                "tl_to_be_ignored_too"
            ]
        }';
        $expected = new Database("192.168.0.2", "prodContao", 3306, "prodContao",
            "admin1234", array("tl_to_be_ignored", "tl_to_be_ignored_too"));
        $mapper = new MapDatabase();

        $actual = $mapper->map(json_decode($input));

        self::assertSame($expected->getServer(), $actual->getServer());
        self::assertSame($expected->getName(), $actual->getName());
        self::assertSame($expected->getPort(), $actual->getPort());
        self::assertSame($expected->getPassword(), $actual->getPassword());
        self::assertSame($expected->getIgnoredTables(), $actual->getIgnoredTables());
    }
}
