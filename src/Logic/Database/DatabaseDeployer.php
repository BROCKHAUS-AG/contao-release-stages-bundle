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

use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\RemoteFilePoller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;
use Exception;

class DatabaseDeployer
{
    private SSHConnector $_sshConnection;
    private Poller $_poller;
    private Config $_config;

    public function __construct(SSHConnector $sshConnection, RemoteFilePoller $poller,  Config $config)
    {
        $this->_sshConnection = $sshConnection;
        $this->_poller = $poller;
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
            $path = $this->_config->getFileServerConfiguration()->getPath();
            $file = $this->getFilePath($runner, $path);
            $this->extractMigrationFile($file, $path, $runner);
            $this->_poller->pollFile("$path". Constants::SCRIPT_DIRECTORY_PROD. "/un_archive_". Constants::DATABASE_MIGRATION_FILE_COMPRESSED);
        } catch (Exception $e) {
            throw new DatabaseDeployment("Couldn't deploy file system: $e");
        }finally {
            $this->_sshConnection->disconnect();
        }
    }

    private function getFilePath(SSHRunner $runner, string $path): string
    {
        return $runner->getPathOfLatestFileWithPattern($path. str_replace("%timestamp%_", "*",
                Constants::DATABASE_MIGRATION_FILE_PROD));
    }

    private function extractMigrationFile(string $file, string $path, SSHRunner $runner): void
    {
        $tags = $this->createTags($file, $path);
        $scriptPath = "$path". Constants::UN_ARCHIVE_SCRIPT_PROD;
        $runner->executeBackgroundScript($scriptPath, $tags);
    }

    private function createTags(string $file, string $path): array
    {
        return array(
            "-f \"$file\"",
            "-e \"$path/migrations/database_migration\"",
            "-n \"". Constants::DATABASE_MIGRATION_FILE_COMPRESSED. "\""
        );
    }
}
