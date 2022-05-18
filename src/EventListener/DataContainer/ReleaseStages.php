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
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\Versioning;
use Exception;

class ReleaseStages
{
    private ScriptFileSynchronizer $_scriptFileSynchronizer;
    private Versioning $_versioning;
    private BackupCreator $_backupCreator;
    private DatabaseCopier $_databaseCopier;
    private FileServerCopier $_fileServerCopier;

    public function __construct(ScriptFileSynchronizer $scriptFileSynchronizer, Versioning $versioning,
                                BackupCreator $backupCreator, DatabaseCopier $databaseCopier,
                                FileServerCopier $fileServerCopier)
    {
        $this->_scriptFileSynchronizer = $scriptFileSynchronizer;
        $this->_versioning = $versioning;
        $this->_backupCreator = $backupCreator;
        $this->_databaseCopier = $databaseCopier;
        $this->_fileServerCopier = $fileServerCopier;
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
        $this->_versioning->generateNewVersionNumber();
        try {
            $this->_scriptFileSynchronizer->synchronize();
        } catch (Exception $e) {
            echo $e;
        }

        // ghost/zombie tasks
        //$this->_backupCreator->create();
        die();

        // try
        // erzeugt eine Datenbank-update Skript, in der alle Befehle stehen die in der prod db stehen sollen
        // das Ausführen des Skriptes passiert noch nicht hier, sondern erst in remote installation
        // ghost/zombie tasks
        // $this->_databaseCopier->copy();
        // alle Dateien, auch einzelne Dateien sollen gezippt werden, also auch das Datenbank-Update Skript
        // nach der Übertragung zum Prod Server, werden die gezippte Dateien auf dem Prod Server entpackt (prüfen, ob entpacken erfolgreich)
        // ghost/zombie tasks
        // $this->_fileServerCopier->copy();
        // ghost/zombie tasks
        // this execute remote installation

        // catch
        // recover backup
    }
}
