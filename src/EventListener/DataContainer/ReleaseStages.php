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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseExecutionFailure;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCopy;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCreateDirectory;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Validation;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Backup\BackupCreator;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseCopier;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\FileServerCopier;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\ScriptFileSynchronizer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\StateSynchronizer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\Versioning;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemVariables;
use Exception;

class ReleaseStages
{
    private ScriptFileSynchronizer $_scriptFileSynchronizer;
    private Versioning $_versioning;
    private BackupCreator $_backupCreator;
    private DatabaseCopier $_databaseCopier;
    private FileServerCopier $_fileServerCopier;
    private StateSynchronizer $_stateSynchronizer;

    public function __construct(ScriptFileSynchronizer $scriptFileSynchronizer, Versioning $versioning,
                                BackupCreator $backupCreator, DatabaseCopier $databaseCopier,
                                FileServerCopier $fileServerCopier, StateSynchronizer $stateSynchronizer)
    {
        $this->_scriptFileSynchronizer = $scriptFileSynchronizer;
        $this->_versioning = $versioning;
        $this->_backupCreator = $backupCreator;
        $this->_databaseCopier = $databaseCopier;
        $this->_fileServerCopier = $fileServerCopier;
        $this->_stateSynchronizer = $stateSynchronizer;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DatabaseQueryEmptyResult
     * @throws DatabaseExecutionFailure
     * @throws FTPCopy
     * @throws FTPCreateDirectory
     * @throws Validation
     *
     * This method is called when clicking the submit button in the Release Stages DCA
     * After clicking the button, the Bundle would create a new Release
     */
    public function onSubmitCallback() : void
    {
        $latestState = $this->_stateSynchronizer->checkLatestState();
        var_dump($latestState);


        $id = $this->_versioning->generateNewVersionNumber();
        try {
            $this->_scriptFileSynchronizer->synchronize();
        } catch (Exception $e) {
            $this->_stateSynchronizer->setState(SystemVariables::STATE_FAILURE, $id);
            echo $e;
            die("File Synchronization failed.");
        }
        $this->_stateSynchronizer->setState(SystemVariables::STATE_SUCCESS, $id);

        die;
    }
}
