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
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use Doctrine\DBAL\Exception;

class StateSynchronizer
{
    private Database $_database;

    public function __construct(Database $database)
    {
        $this->_database = $database;
    }

    /**
     * @throws DatabaseQueryEmptyResult
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getActualId(): int
    {
        return $this->_database->getActualIdFromTable(Constants::DEPLOYMENT_TABLE);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function isOldDeploymentPending(int $actualId): bool
    {
        return $this->_database->isOldDeploymentPending($actualId);
    }

    /**
     * @throws Exception
     */
    public function updateState(string $state, int $id, int $executionTime, string $information=""): void
    {
        $this->_database->updateState($state, $id, $executionTime, $information);
    }
}
