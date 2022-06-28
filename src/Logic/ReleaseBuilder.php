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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\ReleaseBuild;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Backup\BackupCreator;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Migrator\DatabaseMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileSystem\Migrator\FileSystemMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\ScriptFileSynchronizer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\StateSynchronizer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\Versioning;
use Exception;

class ReleaseBuilder
{
    private ScriptFileSynchronizer $_scriptFileSynchronizer;
    private Versioning $_versioning;
    private BackupCreator $_backupCreator;
    private DatabaseMigrationBuilder $_databaseMigrationBuilder;
    private FileSystemMigrationBuilder $_fileSystemMigrationBuilder;
    private StateSynchronizer $_stateSynchronizer;

    public function __construct(ScriptFileSynchronizer $scriptFileSynchronizer, Versioning $versioning,
                                BackupCreator $backupCreator, DatabaseMigrationBuilder $databaseMigrationBuilder,
                                FileSystemMigrationBuilder $fileSystemMigrationBuilder,
                                StateSynchronizer $stateSynchronizer)
    {
        $this->_scriptFileSynchronizer = $scriptFileSynchronizer;
        $this->_versioning = $versioning;
        $this->_backupCreator = $backupCreator;
        $this->_databaseMigrationBuilder = $databaseMigrationBuilder;
        $this->_fileSystemMigrationBuilder = $fileSystemMigrationBuilder;
        $this->_stateSynchronizer = $stateSynchronizer;
    }

    /**
     * @throws ReleaseBuild
     */
    public function build(int $actualId): bool
    {
        try {
            return $this->buildDeployment($actualId);
        }catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new ReleaseBuild("Failed to build release: $e");
        }
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function buildDeployment(int $actualId): bool
    {
        if ($this->_stateSynchronizer->isOldDeploymentPending($actualId)) {
            return false;
        }
        $this->_versioning->generateNewVersionNumber($actualId);
        $this->_scriptFileSynchronizer->synchronize();
        $this->_backupCreator->create();
        $this->_databaseMigrationBuilder->buildAndCopy();
        $this->_fileSystemMigrationBuilder->buildAndCopy();
        return true;
    }
}
