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

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\MapLocal;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;
use Contao\TestCase\ContaoTestCase;

/**
 * Class MapLocalTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Tests\Mapper
 */
class MapLocalTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        self::assertInstanceOf(MapLocal::class, new MapLocal());
    }

    public function testMap() : void
    {
        $input = '{ "contaoProdPath": "test" }';
        $expected = new Local("test");
        $mapper = new MapLocal();

        $actual = $mapper->map(json_decode($input))->getContaoProdPath();

        self::assertSame($expected->getContaoProdPath(), $actual);
    }
}
