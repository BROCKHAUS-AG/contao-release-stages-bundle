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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Database;

use BrockhausAg\ContaoReleaseStagesBundle\Constants\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsProdStage;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsTestStage;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Migrator\DatabaseMigrator;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Extractor;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;
use Exception;

class DatabaseDeployer
{
    private SSHConnector $_sshConnection;
    private Extractor $_extractor;
    private Config $_config;
    private DatabaseMigrator $_databaseMigrator;

    public function __construct(SSHConnector $sshConnection, Extractor $extractor, DatabaseMigrator $databaseMigrator,
                                Config $config)
    {
        $this->_sshConnection = $sshConnection;
        $this->_extractor = $extractor;
        $this->_databaseMigrator = $databaseMigrator;
        $this->_config = $config;
    }

    /**
     * @throws SSHConnection
     * @throws DatabaseDeployment
     */
    public function deploy(): void
    {
        $runner = $this->_sshConnection->connect();
        try {
            $path = $this->_config->getFileServerConfiguration()->getRootPath();
            $this->extract($runner, $path);
            $this->_databaseMigrator->migrate($runner, ConstantsTestStage::DATABASE_MIGRATION_FILE);
        } catch (Exception $e) {
            throw new DatabaseDeployment("Couldn't deploy database: $e");
        }finally {
            $this->_sshConnection->disconnect();
        }
    }

    private function extract(SSHRunner $runner, string $path): void
    {
        $file = $this->getFilePath($runner, $path);
        $extractedPath = $path. ConstantsProdStage::DATABASE_EXTRACTED_MIGRATION_DIRECTORY;
        $this->_extractor->extract($runner, $file, $extractedPath,
            ConstantsProdStage::DATABASE_MIGRATION_FILE_COMPRESSED, $path);
    }

    private function getFilePath(SSHRunner $runner, string $path): string
    {
        return $runner->getPathOfLatestFileWithPattern($path. str_replace(
            Constants::FILE_TIMESTAMP_PATTERN, "*",
            ConstantsProdStage::DATABASE_MIGRATION_FILE));
    }
}
