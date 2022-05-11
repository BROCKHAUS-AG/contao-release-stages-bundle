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

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPRunner;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemVariables;

define("BACKUP_PATH", "backup/");
define("DB_BACKUP_FILE", "backup_database.sh");
define("FILE_SYSTEM_BACKUP_FILE", "backup_file_system.sh");
class BackupCreator
{
    private SSHConnector $_sshConnection;
    private IO $_io;
    private string $_path;
    private FTPConnector $_ftpConnector;
    private FTPRunner $_ftpRunner;
    private Log $_log;

    public function __construct(SSHConnector $sshConnection, IO $io, string $path, FTPConnector $ftpConnector, Log $log)
    {
        $this->_sshConnection = $sshConnection;
        $this->_io = $io;
        $this->_path = $path;
        $this->_ftpConnector = $ftpConnector;
        $this->_log = $log;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function setUp(): void
    {
        $ftpConnection = $this->_ftpConnector->connect();
        $this->_ftpRunner = new FTPRunner($ftpConnection);
    }

    public function create(): void
    {
        $sshExecution = $this->getSSHRunner();
        $this->checkIfBackupExecutionFilesExistsElseCreateIt($sshExecution);
        $this->createBackupFromDB($sshExecution);
        $this->createBackupFromFileSystem($sshExecution);
    }

    private function checkIfBackupExecutionFilesExistsElseCreateIt(SSHRunner $runner): void
    {
        if ($this->checkIfFileExists($runner, $this->getDBBackupFilePath())) {
            $this->copyFileToPathAtProd(DB_BACKUP_FILE, $this->getBackupPath());
        }
        if ($this->checkIfFileExists($runner, $this->getFileSystemBackupFilePath())) {
            $this->copyFileToPathAtProd(FILE_SYSTEM_BACKUP_FILE, $this->getBackupPath());
        }
    }

    private function copyFileToPathAtProd(string $fileName, string $prodPath): void
    {
        $localPath = $this->_path. SystemVariables::PATH_TO_VENDOR. $fileName;
        $file = new File(0, $localPath, $prodPath);
        $this->_ftpRunner->copy($file);
        $this->_log->info("Successfully copied \"". $fileName. "\" to prod path \"". $prodPath. "\"");
    }

    private function checkIfFileExists(SSHRunner $runner, string $file): bool
    {
        $stream = $runner->execute("cat ". $file);
        $output = $runner->getResponse($stream);
        return $output != "cat: ". $file. ": No such file or directory";
    }

    private function getSSHRunner(): SSHRunner
    {
        return new SSHRunner($this->_sshConnection);
    }

    private function createBackupFromDB(SSHRunner $runner): void
    {
        $runner->executeScript($this->getDBBackupFilePath());
    }

    private function createBackupFromFileSystem(SSHRunner $runner): void
    {
        $runner->executeScript($this->getFileSystemBackupFilePath());
    }

    private function getDBBackupFilePath(): string
    {
        return SystemVariables::BACKUP_DATABASE_SCRIPT;
    }

    private function getFileSystemBackupFilePath(): string
    {
        return SystemVariables::BACKUP_FILE_SYSTEM_SCRIPT;
    }

    private function getBackupPath(): string
    {
        return SystemVariables::BACKUP_DIRECTORY;
    }
}
