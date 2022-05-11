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
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPFileServerCopier;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHExecution;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;

define("BACKUP_PATH", "backup/");
define("DB_BACKUP_FILE", "backup_database.sh");
define("FILE_SYSTEM_BACKUP_FILE", "backup_file_system.sh");
define("LOCAL_PATH", "vendor/brockhaus-ag/contao-release-stages/scripts/backup/");
class BackupCreator
{
    private SSHConnector $_sshConnection;
    private IO $_io;
    private string $_path;
    private FTPConnector $_ftpConnector;
    private FTPFileServerCopier $_ftpFileServerCopier;
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
        $this->_ftpFileServerCopier = new FTPFileServerCopier($ftpConnection);
    }

    public function create(): void
    {
        $sshExecution = $this->getSSHExecution();
        $this->checkIfBackupExecutionFilesExistsElseCreateIt($sshExecution);
        $this->createBackupFromDB($sshExecution);
        $this->createBackupFromFileSystem($sshExecution);
    }

    private function checkIfBackupExecutionFilesExistsElseCreateIt(SSHExecution $execution): void
    {
        if ($this->checkIfFileExists($execution, $this->getDBBackupFilePath())) {
            $this->copyFileToPathAtProd(DB_BACKUP_FILE, $this->getBackupPath());
        }
        if ($this->checkIfFileExists($execution, $this->getFileSystemBackupFilePath())) {
            $this->copyFileToPathAtProd(FILE_SYSTEM_BACKUP_FILE, $this->getBackupPath());
        }
    }

    private function copyFileToPathAtProd(string $fileName, string $prodPath): void
    {
        $localPath = $this->_path. LOCAL_PATH. $fileName;
        $file = new File(0, $localPath, $prodPath);
        $this->_ftpFileServerCopier->copy($file);
        $this->_log->info("Successfully copied \"". $fileName. "\" to prod path \"". $prodPath. "\"");
    }

    private function checkIfFileExists(SSHExecution $execution, string $file): bool
    {
        $stream = $execution->execute("cat ". $file);
        $output = $execution->getResponse($stream);
        return $output != "cat: ". $file. ": No such file or directory";
    }

    private function getSSHExecution(): SSHExecution
    {
        return new SSHExecution($this->_sshConnection);
    }

    private function createBackupFromDB(SSHExecution $execution): void
    {
        $execution->executeScript($this->getDBBackupFilePath());
    }

    private function createBackupFromFileSystem(SSHExecution $execution): void
    {
        $execution->executeScript($this->getFileSystemBackupFilePath());
    }

    private function getDBBackupFilePath(): string
    {
        return $this->getBackupPath().DB_BACKUP_FILE;
    }

    private function getFileSystemBackupFilePath(): string
    {
        return $this->getBackupPath().FILE_SYSTEM_BACKUP_FILE;
    }

    private function getBackupPath(): string
    {
        return $this->_io->getFileServerConfiguration()->getPath().BACKUP_PATH;
    }
}
