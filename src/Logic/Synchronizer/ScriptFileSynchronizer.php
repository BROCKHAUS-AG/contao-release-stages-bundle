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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCopy;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCreateDirectory;
use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Synchronize;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\AbstractFTPRunner;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use BrockhausAg\ContaoReleaseStagesBundle\Model\FileCollection;
use Exception;

class ScriptFileSynchronizer
{
    private string $_path;
    private string $_fileServerPath;
    private FTPConnector $_ftpConnector;

    public function __construct(string $path, FTPConnector $ftpConnector, Config $config)
    {
        $this->_path = $path;
        $this->_fileServerPath = $config->getFileServerConfiguration()->getPath();
        $this->_ftpConnector = $ftpConnector;
    }

    /**
     * @throws FTPConnection
     * @throws Synchronize
     */
    public function synchronize(): void
    {
        $ftpRunner = $this->_ftpConnector->connect();
        try {
            $this->createDirectories($ftpRunner);
            $this->copyScriptFiles($ftpRunner);
        }catch (Exception $e) {
            throw new Synchronize("Failed to synchronize directories or/and files");
        }finally {
            $this->_ftpConnector->disconnect($ftpRunner->getConn());
        }
    }

    /**
     * @throws FTPCreateDirectory
     */
    private function createDirectories(AbstractFTPRunner $runner): void
    {
        $directories = $this->createDirectoryCollection();
        foreach ($directories as $directory) {
            $runner->createDirectory($directory);
        }
    }

    private function createDirectoryCollection(): array
    {
        return array(
            $this->_fileServerPath. Constants::SCRIPT_DIRECTORY_PROD,
            $this->_fileServerPath. Constants::BACKUP_DIRECTORY_PROD
        );
    }

    /**
     * @throws FTPCopy
     */
    private function copyScriptFiles(AbstractFTPRunner $runner): void
    {
        $files = $this->createFileCollection();
        foreach ($files->get() as $file) {
            $runner->copy($file);
        }
    }

    private function createFileCollection(): FileCollection
    {
        $files = new FileCollection();
        $files->add($this->getDatabaseScriptFile());
        $files->add($this->getFileServerScriptFile());
        $files->add($this->getCreateStateScriptFile());
        $files->add($this->getUnArchiveScriptFile());
        $files->add($this->getMigrateDatabaseScriptFile());
        return $files;
    }

    private function getDatabaseScriptFile(): File
    {
        return new File($this->_path. Constants::BACKUP_DATABASE_SCRIPT,
            $this->_fileServerPath. Constants::BACKUP_DATABASE_SCRIPT_PROD);
    }

    private function getFileServerScriptFile(): File
    {
        return new File($this->_path. Constants::BACKUP_FILE_SYSTEM_SCRIPT,
            $this->_fileServerPath. Constants::BACKUP_FILE_SYSTEM_SCRIPT_PROD);
    }

    private function getCreateStateScriptFile(): File
    {
        return new File($this->_path. Constants::CREATE_STATE_SCRIPT,
            $this->_fileServerPath. Constants::CREATE_STATE_SCRIPT_PROD);
    }

    private function getUnArchiveScriptFile(): File
    {
        return new File($this->_path. Constants::UN_ARCHIVE_SCRIPT,
            $this->_fileServerPath. Constants::UN_ARCHIVE_SCRIPT_PROD);
    }

    private function getMigrateDatabaseScriptFile(): File
    {
        return new File($this->_path. Constants::MIGRATE_DATABASE_SCRIPT,
            $this->_fileServerPath. Constants::MIGRATE_DATABASE_SCRIPT_PROD);
    }
}
