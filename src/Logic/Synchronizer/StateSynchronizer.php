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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\State\NoSubmittedPendingState;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\State\OldStateIsPending;
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
        return $this->_database->getActualIdFromTlReleaseStages();
    }

    /**
     * @throws OldStateIsPending
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DatabaseQueryEmptyResult
     * @throws \Doctrine\DBAL\Exception
     * @throws NoSubmittedPendingState
     */
    public function checkLatestState(): void
    {
        $this->_database->checkLatestState();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function setState(string $state, int $id): void
    {
        $this->_database->updateState($state, $id);
    }
}
