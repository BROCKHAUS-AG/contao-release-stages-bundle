<?php

declare(strict_types=1);

/*
 * This file is part of contao-release-stages-bundle.
 *
 * (c) BROCKHAUS AG 2022 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-release-stages-bundle
 */

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer;

use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\State\OldDeploymentStateIsPending;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;

class StateSynchronizer
{
    private Database $_database;

    public function __construct(Database $database)
    {
        $this->_database = $database;
    }

    /**
     * @throws DatabaseQueryEmptyResult
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getActualId(): int
    {
        return $this->_database->getActualIdFromTable(Constants::DEPLOYMENT_TABLE);
    }

    /**
     * @throws OldDeploymentStateIsPending
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function breakDeploymentIfOldDeploymentIsPending(int $actualId): void
    {
        if ($this->isOldDeploymentPending($actualId)) {
            $this->setState(Constants::STATE_OLD_PENDING, $actualId);
            throw new OldDeploymentStateIsPending("An old release is pending");
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function setState(string $state, int $id): void
    {
        $this->_database->updateState($state, $id);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function isOldDeploymentPending(int $actualId): bool
    {
        return $this->_database->isOldDeploymentPending($actualId);
    }
}
