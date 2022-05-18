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

use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemVariables;

class BackupCreator
{
    private SSHConnector $_sshConnection;
    private IO $_io;

    public function __construct(SSHConnector $sshConnection, IO $io)
    {
        $this->_sshConnection = $sshConnection;
        $this->_io = $io;
    }

    public function create(): void
    {
        $path = $this->_io->getFileServerConfiguration()->getPath();
        $runner = $this->getSSHRunner();
        $this->createDatabaseBackup($path, $runner);
        $this->createFileServerBackup($path, $runner);
    }

    private function createDatabaseBackup(string $path, SSHRunner $runner): void
    {
        $runner->executeScript($path. SystemVariables::BACKUP_DATABASE_SCRIPT_PROD);
    }

    private function createFileServerBackup(string $path, SSHRunner $runner): void
    {
        $runner->executeScript($path. SystemVariables::BACKUP_FILE_SYSTEM_SCRIPT_PROD);
    }

    private function getSSHRunner(): SSHRunner
    {
        return new SSHRunner($this->_sshConnection);
    }
}
