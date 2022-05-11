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
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Backup\BackupCreator;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseCopier;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\FileServerCopier;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\Versioning;
use Doctrine\DBAL\Exception;

class ReleaseStages
{
    private Versioning $_versioning;
    private BackupCreator $_backupCreator;
    private DatabaseCopier $_databaseCopier;
    private FileServerCopier $_fileServerCopier;

    public function __construct(Versioning $versioning, BackupCreator $backupCreator, DatabaseCopier $databaseCopier,
                                FileServerCopier $fileServerCopier)
    {
        $this->_versioning = $versioning;
        $this->_backupCreator = $backupCreator;
        $this->_databaseCopier = $databaseCopier;
        $this->_fileServerCopier = $fileServerCopier;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DatabaseQueryEmptyResult
     * @throws DatabaseExecutionFailure
     *
     * This method is called when clicking the submit button in the Release Stages DCA
     * After clicking the button, the Bundle would create a new Release
     */
    public function onSubmitCallback() : void
    {
        $this->_backupCreator->create();
        // $this->_databaseCopier->copy();
        // $this->_fileServerCopier->copy();
        $this->_versioning->generateNewVersionNumber();
    }
}
