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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPConnetion;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCopy;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCreateDirectory;
use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPRunner;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\AbstractFTPRunner;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\SFTPRunner;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use BrockhausAg\ContaoReleaseStagesBundle\Model\FileCollection;

class ScriptFileSynchronizer
{
    private string $_path;
    private FTPConnector $_ftpConnector;
    private Config $_config;

    public function __construct(string $path, FTPConnector $ftpConnector, Config $config)
    {
        $this->_path = $path;
        $this->_ftpConnector = $ftpConnector;
        $this->_config = $config;
    }

    /**
     * @throws FTPCopy
     * @throws FTPCreateDirectory
     * @throws FTPConnetion
     */
    public function synchronize(): void
    {
        $ftpRunner = $this->getFTPRunner();

        $this->createDirectories($ftpRunner);
        $this->copyScriptFiles($ftpRunner);
    }

    /**
     * @throws FTPConnetion
     */
    private function getFTPRunner(): AbstractFTPRunner
    {
        if ($this->_config->getFTPConfiguration()->isSsl()) {
            return new SFTPRunner($this->_ftpConnector->connect());
        }
        return new FTPRunner($this->_ftpConnector->connect());
    }

    /**
     * @throws FTPCreateDirectory
     */
    private function createDirectories(AbstractFTPRunner $runner): void
    {
        $fileServerConfigurationPath = $this->_config->getFileServerConfiguration()->getPath();
        $directories = array(
            $fileServerConfigurationPath. Constants::SCRIPT_DIRECTORY_PROD,
            $fileServerConfigurationPath. Constants::BACKUP_DIRECTORY_PROD
        );

        foreach ($directories as $directory) {
            $runner->createDirectory($directory);
        }
    }

    /**
     * @throws FTPCopy
     */
    private function copyScriptFiles(AbstractFTPRunner $runner): void
    {
        $files = $this->createFiles();
        foreach ($files->get() as $file) {
            $runner->copy($file);
        }
    }

    private function createFiles(): FileCollection
    {
        $files = new FileCollection();
        $files->add(new File($this->getFullBackupDatabaseScriptPath(), $this->getFullProdBackupDatabaseScriptPath()));
        $files->add(new File($this->getFullFileServerScriptPath(), $this->getFullProdFileServerScriptPath()));
        $files->add(new File($this->getFullCreateStateScriptPath(), $this->getFullProdCreateStateScriptPath()));
        return $files;
    }

    private function getFullBackupDatabaseScriptPath(): string
    {
        return $this->_path. Constants::BACKUP_DATABASE_SCRIPT;
    }

    private function getFullProdBackupDatabaseScriptPath(): string
    {
        return $this->_config->getFileServerConfiguration()->getPath(). Constants::BACKUP_DATABASE_SCRIPT_PROD;
    }

    private function getFullFileServerScriptPath(): string
    {
        return $this->_path. Constants::BACKUP_FILE_SYSTEM_SCRIPT;
    }

    private function getFullProdFileServerScriptPath(): string
    {
        return $this->_config->getFileServerConfiguration()->getPath(). Constants::BACKUP_FILE_SYSTEM_SCRIPT_PROD;
    }

    private function getFullCreateStateScriptPath(): string
    {
        return $this->_path. Constants::CREATE_STATE_SCRIPT;
    }

    private function getFullProdCreateStateScriptPath(): string
    {
        return $this->_config->getFileServerConfiguration()->getPath(). Constants::CREATE_STATE_SCRIPT_PROD;
    }
}
