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

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\Logic\Versioning;

use BrockhausAg\ContaoReleaseStagesBundle\Constants\DeploymentState;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\Versioning;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Version\Version;
use Contao\TestCase\ContaoTestCase;

/**
 * Class VersioningTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\
 */
class VersioningTest extends ContaoTestCase
{
    private Versioning $versioning;

    public function setUp(): void
    {
        $databaseMock = self::createMock(Database::class);
        $logMock = self::createMock(Logger::class);
        $this->versioning = new Versioning($databaseMock, $logMock);

        parent::setUp();
    }

    public function testInstantiation(): void
    {
        $versioningMock = self::createMock(Versioning::class);
        self::assertInstanceOf(Versioning::class, $versioningMock);
    }

    public function testCreateVersionNumber_createNewReleaseVersion(): void
    {
        $expected = "1.1";

        $version = new Version(1, "release", "1.0", DeploymentState::PENDING);
        $actual = $this->versioning->createVersionNumber($version, "release");
        self::assertSame($expected, $actual);
    }

    public function testCreateVersionNumber_createNewMajorReleaseVersion(): void
    {
        $expected = "2.0";

        $version = new Version(1, "release", "1.3", DeploymentState::PENDING);
        $actual = $this->versioning->createVersionNumber($version, "majorRelease");
        self::assertSame($expected, $actual);
    }
}
