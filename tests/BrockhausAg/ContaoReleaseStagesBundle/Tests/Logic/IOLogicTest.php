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

use BrockhausAg\ContaoReleaseStagesBundle\Logic\IOLogic;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemConfig;
use Contao\TestCase\ContaoTestCase;

/**
 * Class IOLogicTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Logic\
 */
class IOLogicTest extends ContaoTestCase
{
    protected IOLogic $_ioLogic;

    protected function setUp(): void
    {
        parent::setUp();
        $systemConfigMock = self::createMock(SystemConfig::class);

        $this->_ioLogic = new IOLogic("test", $systemConfigMock);
    }


    public function testInstantiation(): void
    {
        $systemConfigMock = self::createMock(SystemConfig::class);
        self::assertInstanceOf(IOLogic::class, new IOLogic("", $systemConfigMock));
    }


    public function testLoadContaoPath(): void
    {
        $expected = "test/files";

        $actual = $this->_ioLogic->loadPathToContaoFiles();

        self::assertSame($expected, $actual);
    }

    public function testLoadDatabaseConfiguration(): void
    {

    }
}
