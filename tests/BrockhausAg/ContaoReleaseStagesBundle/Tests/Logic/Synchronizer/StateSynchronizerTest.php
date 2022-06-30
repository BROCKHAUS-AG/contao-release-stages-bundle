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

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\Logic\Synchronizer;

use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\StateSynchronizer;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Exception;

/**
 * Class StateSynchronizerTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\
 */
class StateSynchronizerTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        self::assertInstanceOf(StateSynchronizer::class, self::createMock(StateSynchronizer::class));
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function testIfIsDeploymentReturnTrue(): void
    {
        $databaseMock = self::createMock(Database::class);
        $databaseMock->method("isOldDeploymentPending")->willReturn(true);

        $stateSynchronizer = new StateSynchronizer($databaseMock);

        self::assertTrue($stateSynchronizer->isOldDeploymentPending(1));
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function testIfIsDeploymentReturnFalse(): void
    {
        $databaseMock = self::createMock(Database::class);
        $databaseMock->method("isOldDeploymentPending")->willReturn(false);

        $stateSynchronizer = new StateSynchronizer($databaseMock);

        self::assertFalse($stateSynchronizer->isOldDeploymentPending(1));
    }
}
