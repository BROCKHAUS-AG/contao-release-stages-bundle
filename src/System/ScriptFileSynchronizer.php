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

namespace BrockhausAg\ContaoReleaseStagesBundle\System;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCopy;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPRunner;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use BrockhausAg\ContaoReleaseStagesBundle\Model\FileCollection;


class ScriptFileSynchronizer
{
    private string $_path;
    private FTPConnector $_ftpConnector;
    private IO $_io;

    public function __construct(string $path, FTPConnector $ftpConnector, IO $io)
    {
        $this->_path = $path;
        $this->_ftpConnector = $ftpConnector;
        $this->_io = $io;
    }

    /**
     * @throws FTPCopy
     */
    public function synchronize(): void
    {
        $ftpRunner = $this->getFTPRunner();

        $this->createDirectories($ftpRunner);
        $this->copyScriptFiles($ftpRunner);
    }

    private function getFTPRunner(): FTPRunner
    {
        return new FTPRunner($this->_ftpConnector->connect());
    }

    private function createDirectories(FTPRunner $ftpRunner): void
    {
        $fileServerConfigurationPath = $this->_io->getFileServerConfiguration()->getPath();
        $directories = array(
            $fileServerConfigurationPath. SystemVariables::SCRIPT_DIRECTORY_PROD,
            $fileServerConfigurationPath. SystemVariables::BACKUP_DIRECTORY_PROD
        );

        foreach ($directories as $directory) {
            $ftpRunner->createDirectory($directory);
        }
    }

    /**
     * @throws FTPCopy
     */
    private function copyScriptFiles(FTPRunner $ftpRunner): void
    {
        $files = $this->createFiles();
        foreach ($files->get() as $file) {
            $ftpRunner->copy($file);
        }
    }

    private function createFiles(): FileCollection
    {
        $files = new FileCollection();
        $files->add(new File($this->getFullBackupDatabaseScriptPath(), $this->getFullProdBackupDatabaseScriptPath()));
        $files->add(new File($this->getFullFileServerScriptPath(), $this->getFullProdFileServerScriptPath()));
        return $files;
    }

    private function getFullBackupDatabaseScriptPath(): string
    {
        return $this->_path. SystemVariables::BACKUP_DATABASE_SCRIPT;
    }

    private function getFullProdBackupDatabaseScriptPath(): string
    {
        return $this->_io->getFileServerConfiguration()->getPath(). SystemVariables::BACKUP_DATABASE_SCRIPT_PROD;
    }

    private function getFullFileServerScriptPath(): string
    {
        return $this->_path. SystemVariables::BACKUP_FILE_SYSTEM_SCRIPT;
    }

    private function getFullProdFileServerScriptPath(): string
    {
        return $this->_io->getFileServerConfiguration()->getPath(). SystemVariables::BACKUP_FILE_SYSTEM_SCRIPT_PROD;
    }
}
