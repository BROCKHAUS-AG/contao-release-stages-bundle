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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPConnetion;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCreateDirectory;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPRunner;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Local\FileServerLocalCopier;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use BrockhausAg\ContaoReleaseStagesBundle\Model\FileCollection;
use Contao\Backend;

DEFINE("COPY_TO_LOCAL", "local");
DEFINE("COPY_TO_FILE_SERVER", "fileServer");

class FileServerCopier extends Backend {
    private Logger $_logger;
    private Config $_config;
    private FileServerLocalCopier $_localFileServerCopier;
    private FTPRunner $_ftpFileServerCopier;
    private Database $_database;

    private string $copyTo;

    public function __construct(Database $database, Config $config, FileServerLocalCopier $localFileServerCopier, Logger $logger)
    {
        $this->_database = $database;
        $this->_config = $config;
        $this->_localFileServerCopier = $localFileServerCopier;
        $this->_logger = $logger;
    }

    /**
     * @throws FTPCreateDirectory
     * @throws FTPConnetion
     */
    public function copy() : void
    {
        $this->copyTo = $this->_config->getWhereToCopy();
        $path = $this->getPathToCopy();
        $loadFromLocalLogic = new LocalLoader($this->_config, $this->_logger,
            $this->_config->getPathToContaoFiles(), $path);
        $files = $loadFromLocalLogic->loadFromLocal();

        $this->createDirectories($files);
        $this->checkForDeletion();
        $this->compareAndCopyFiles($files);
        $this->copyDirectoryToMainDirectoryWithSSHCommand();
    }

    /**
     * @throws FTPConnetion
     */
    private function getPathToCopy() : string
    {
       if ($this->isToCopyToLocalFileServer()) {
            return $this->_config->getLocalFileServerConfiguration()->getContaoProdPath();
        }else if ($this->isToCopyToFTPFileServer()) {
            $ftpConnection = new FTPConnector($this->_config, $this->_logger);
            $this->_ftpFileServerCopier = new FTPRunner($ftpConnection->connect());
            return $this->_config->getFileServerConfiguration()->getPath();
        }
        $this->couldNotFindCopyTo();
        return "";
    }

    /**
     * @throws FTPCreateDirectory
     */
    private function createDirectories(FileCollection $files) : void
    {
        for ($x = 0; $x != count($files->get()); $x++)
        {
            $directories = $this->getDirectoriesFromFilePath($files->getByIndex($x)->getPath());
            foreach ($directories as $directory)
            {
                $this->createDirectory($directory);
            }
        }
    }

    /**
     * @throws FTPCreateDirectory
     */
    private function createDirectory(string $directory) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $this->_localFileServerCopier->createDirectory($directory);
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_ftpFileServerCopier->createDirectory($directory);
        }else {
            $this->couldNotFindCopyTo();
        }
    }

    private function getDirectoriesFromFilePath(string $file) : array
    {
        $directoriesSeparate = explode("/", $file);
        array_splice($directoriesSeparate, 0, 1);
        $directories = array();
        for ($x = 1; $x != sizeof($directoriesSeparate); $x++) {
            $directory = "";
            for ($y = 0; $y != $x; $y++) {
                $directory .= "/". $directoriesSeparate[$y];
            }
            $directories[] = $directory;
        }
        return $directories;
    }

    private function compareAndCopyFiles(FileCollection $files) : void
    {
        for ($x = 0; $x != count($files->get()); $x++)
        {
            $this->checkForUpdate($files[$x]);
            $this->compareAndCopyFile($files[$x]);
        }
    }

    private function compareAndCopyFile(File $file) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $this->_localFileServerCopier->copy($file);
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_ftpFileServerCopier->copy($file);
        }else {
            $this->couldNotFindCopyTo();
        }
    }

    private function checkForUpdate(File $file) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $lastModifiedTime = $this->_localFileServerCopier->getLastModifiedTimeFromFile($file->getProdPath());
            if ($lastModifiedTime < $this->_localFileServerCopier->getLastModifiedTimeFromFile($file->getPath())) {
                $this->_localFileServerCopier->copy($file);
            }
        }else if ($this->isToCopyToFTPFileServer()) {
            $lastModifiedTime = $this->_ftpFileServerCopier->getLastModifiedTimeFromFile($file->getProdPath());
            if ($lastModifiedTime < $this->_ftpFileServerCopier->getLastModifiedTimeFromFile($file->getPath())) {
                $this->_ftpFileServerCopier->update($file);
            }
        }else {
            $this->couldNotFindCopyTo();
        }
    }

    private function checkForDeletion() : void
    {
        $res = $this->_database->checkForDeletedFilesInTlLogTable()
            ->fetchAllAssoc();
        foreach ($res as $file)
        {
            $str = explode("&quot;", $file["text"]);
            $file["text"] = $str[1];
            $fileName = str_replace("files", "", $file["text"]);
            $this->deleteFile($fileName);
        }
    }

    private function deleteFile(string $file) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $this->_localFileServerCopier->delete($file,
                $this->_config->getLocalFileServerConfiguration()->getContaoProdPath());
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_ftpFileServerCopier->delete($file, $this->_config->getFileServerConfiguration()->getPath());
        }else {
            $this->couldNotFindCopyTo();
        }
    }

    private function isToCopyToLocalFileServer() : bool
    {
        return strcmp(COPY_TO_LOCAL, $this->copyTo) == 0;
    }

    private function isToCopyToFTPFileServer() : bool
    {
        return strcmp(COPY_TO_FILE_SERVER, $this->copyTo) == 0;
    }

    private function couldNotFindCopyTo() : void
    {
        $this->_logger->error("Could not find a valid path to update files");
    }

    private function copyDirectoryToMainDirectoryWithSSHCommand() : void
    {
        if ($this->isToCopyToFTPFileServer()) {
            $config = $this->_config->getFileServerConfiguration();
            $config_ssh = $this->_config->getSSHConfiguration();
            $connection = ssh2_connect($config->getServer(), 22);
            ssh2_auth_password($connection, $config_ssh->getUsername(), $config_ssh->getPassword());
            $stream = ssh2_exec($connection, "bash -r /html/release-stages.sh");
            stream_set_blocking($stream, true);
            fclose($stream);
        }
    }
}
