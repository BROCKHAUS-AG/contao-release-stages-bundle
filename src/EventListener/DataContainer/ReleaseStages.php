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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\State\NoSubmittedPendingState;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\State\OldDeploymentStateIsPending;
use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Backup\BackupCreator;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseCopier;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\FileServerCopier;
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
    private DatabaseCopier $_databaseCopier;
    private FileServerCopier $_fileServerCopier;
    private StateSynchronizer $_stateSynchronizer;

    public function __construct(Timer $timer, ScriptFileSynchronizer $scriptFileSynchronizer, Versioning $versioning,
                                BackupCreator $backupCreator, DatabaseCopier $databaseCopier,
                                FileServerCopier $fileServerCopier, StateSynchronizer $stateSynchronizer)
    {
        $this->_timer = $timer;
        $this->_scriptFileSynchronizer = $scriptFileSynchronizer;
        $this->_versioning = $versioning;
        $this->_backupCreator = $backupCreator;
        $this->_databaseCopier = $databaseCopier;
        $this->_fileServerCopier = $fileServerCopier;
        $this->_stateSynchronizer = $stateSynchronizer;
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
            $this->_stateSynchronizer->breakDeploymentIfOldDeploymentIsPending($actualId);
            $this->_versioning->generateNewVersionNumber($actualId);
            $this->_scriptFileSynchronizer->synchronize();
            $this->_backupCreator->create();
            $this->_stateSynchronizer->setState(Constants::STATE_SUCCESS, $actualId);
        }catch (OldDeploymentStateIsPending $e) {
            die($e);
        }catch (Exception $e) {
            $this->_stateSynchronizer->setState(Constants::STATE_FAILURE, $actualId);
        }
    }
}
