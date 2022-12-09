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
namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Backup;

use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsProdStage;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\Poll;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\PollTimeout;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\RemoteFilePoller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;
use Exception;

class BackupCreator
{
    private SSHConnector $_sshConnection;
    private Config $_config;
    private Poller $_poller;

    public function __construct(SSHConnector $sshConnection, Config $config, RemoteFilePoller $poller)
    {
        $this->_sshConnection = $sshConnection;
        $this->_config = $config;
        $this->_poller = $poller;
    }

    /**
     * @throws SSHConnection
     * @throws \BrockhausAg\ContaoReleaseStagesBundle\Exception\BackupCreator
     */
    public function create(): void
    {
        $path = $this->_config->getFileServerConfiguration()->getRootPath();
        $contentPath = $this->_config->getFileServerConfiguration()->getContentPath();
        $runner = $this->_sshConnection->connect();
        try {
            $this->createFileServerBackup($path, $runner);
            $this->createDatabaseBackup($path, $runner);
        }catch (Exception $e) {
            throw new \BrockhausAg\ContaoReleaseStagesBundle\Exception\BackupCreator(
                "Couldn't create backup from database or file server: $e");
        }finally {
            $this->_sshConnection->disconnect();
        }
    }

    /**
     * @throws Poll
     * @throws PollTimeout
     */
    private function createDatabaseBackup(string $path, SSHRunner $runner): void
    {
        $tags = $this->getDatabaseTags($path);
        $runner->executeBackgroundScript($path. ConstantsProdStage::BACKUP_DATABASE_SCRIPT, $tags);
        $this->_poller->pollFile($path. ConstantsProdStage::BACKUP_DATABASE_POLL_FILENAME);
    }

    private function getDatabaseTags($path): array
    {
        $config = $this->_config->getDatabaseConfiguration();
        $username = $config->getUsername();
        $password = $config->getPassword();
        $host = $config->getServer();
        $database = $config->getName();

        return array(
            "-u \"$username\"",
            "-p \"$password\"",
            "-h \"$host\"",
            "-d \"$database\"",
            "-t \"$path/backups\""
        );
    }

    /**
     * @throws Poll
     * @throws PollTimeout
     */
    private function createFileServerBackup(string $path, SSHRunner $runner): void
    {
        $tags = $this->getFileServerTags($path);
        $runner->executeBackgroundScript($path. ConstantsProdStage::BACKUP_FILE_SYSTEM_SCRIPT, $tags);
        $this->_poller->pollFile($path. ConstantsProdStage::BACKUP_FILE_SYSTEM_POLL_FILENAME);
    }

    private function getFileServerTags(string $path): array
    {
        return array(
            "-f \"$path/files/content\"",
            "-t \"$path/backups\""
        );
    }
}
