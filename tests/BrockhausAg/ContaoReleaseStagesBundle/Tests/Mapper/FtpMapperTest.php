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

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\FtpMapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ftp;
use Contao\TestCase\ContaoTestCase;

/**
 * Class FtpMapperTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper
 */
class FtpMapperTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        self::assertInstanceOf(FtpMapper::class, new FtpMapper());
    }

    public function testMap() : void
    {
        $input = '{
            "port": 1234,
            "username": "admin",
            "password": "admin1234",
            "ssl": true
        }';

        $expected = new Ftp(1234, "admin", "admin1234", true);

        $mapper = new FtpMapper();
        $actual = $mapper->map(json_decode($input));


        self::assertSame($expected->getPort(), $actual->getPort());
        self::assertSame($expected->getUsername(), $actual->getUsername());
        self::assertSame($expected->getPassword(), $actual->getPassword());
        self::assertSame($expected->isSsl(), $actual->isSsl());
    }
}
