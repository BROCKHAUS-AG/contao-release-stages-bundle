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

namespace BrockhausAg\ContaoReleaseStagesBundle\EventListener\DataContainer;

use BrockhausAg\ContaoReleaseStagesBundle\Logic\Backup\BackupCreator;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Migrator\DatabaseMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileSystem\Migrator\FileSystemMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Finisher;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\ScriptFileSynchronizer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\StateSynchronizer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Timer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\Versioning;
use Exception;

class ReleaseStages
{
    private Timer $_timer;
    private ScriptFileSynchronizer $_scriptFileSynchronizer;
    private Versioning $_versioning;
    private BackupCreator $_backupCreator;
    private DatabaseMigrationBuilder $_databaseMigrationBuilder;
    private FileSystemMigrationBuilder $_fileSystemMigrationBuilder;
    private StateSynchronizer $_stateSynchronizer;
    private Finisher $_finisher;

    public function __construct(Timer $timer, ScriptFileSynchronizer $scriptFileSynchronizer, Versioning $versioning,
                                BackupCreator $backupCreator, DatabaseMigrationBuilder $databaseMigrationBuilder,
                                FileSystemMigrationBuilder $fileSystemMigrationBuilder,
                                StateSynchronizer $stateSynchronizer, Finisher $finisher)
    {
        $this->_timer = $timer;
        $this->_scriptFileSynchronizer = $scriptFileSynchronizer;
        $this->_versioning = $versioning;
        $this->_backupCreator = $backupCreator;
        $this->_databaseMigrationBuilder = $databaseMigrationBuilder;
        $this->_fileSystemMigrationBuilder = $fileSystemMigrationBuilder;
        $this->_stateSynchronizer = $stateSynchronizer;
        $this->_finisher = $finisher;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     *
     * This method is called when clicking the submit button in the Release Stages DCA
     * After clicking the button, the Bundle would create a new Release
     */
    public function onSubmitCallback(): void
    {
        $this->_timer->start();
        $actualId = $this->_stateSynchronizer->getActualId();
        try {
            if ($this->_stateSynchronizer->isOldDeploymentPending($actualId)) {
                $this->_finisher->finishWithOldDeploymentIsPending($actualId);
                return;
            }
            $this->_versioning->generateNewVersionNumber($actualId);
            $this->_scriptFileSynchronizer->synchronize();
            $this->_backupCreator->create();
            $this->_databaseMigrationBuilder->buildAndCopy();
            $this->_fileSystemMigrationBuilder->buildAndCopy();
            $this->_finisher->finishWithSuccess($actualId);
        }catch (Exception $e) {
            $this->_finisher->finishWithFailure($actualId);
            // ToDo: die is only for development
            die($e);
        }
        // ToDo: die is only for development
        die;
    }
}
