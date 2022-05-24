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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;

class BackupCreator
{
    private SSHConnector $_sshConnection;
    private Config $_config;

    public function __construct(SSHConnector $sshConnection, Config $config)
    {
        $this->_sshConnection = $sshConnection;
        $this->_config = $config;
    }

    /**
     * @throws SSHConnection
     */
    public function create(): void
    {
        $path = $this->_config->getFileServerConfiguration()->getPath();
        $runner = $this->getSSHRunner();
        $this->createDatabaseBackup($path, $runner);
        $this->createFileServerBackup($path, $runner);
    }

    private function createDatabaseBackup(string $path, SSHRunner $runner): void
    {
        $runner->executeScript($path. Constants::BACKUP_DATABASE_SCRIPT_PROD);
    }

    private function createFileServerBackup(string $path, SSHRunner $runner): void
    {
        $runner->executeScript($path. Constants::BACKUP_FILE_SYSTEM_SCRIPT_PROD);
    }

    /**
     * @throws SSHConnection
     */
    private function getSSHRunner(): SSHRunner
    {
        return new SSHRunner($this->_sshConnection->connect());
    }
}
