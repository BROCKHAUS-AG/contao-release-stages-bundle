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

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\FTP\FileServerCopier;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\FTP\FTPConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\Local\LocalFileServerCopier;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\FileCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use Contao\Backend;

DEFINE("COPY_TO_LOCAL", "local");
DEFINE("COPY_TO_FILE_SERVER", "fileServer");

class FileServerCopier extends Backend {
    private Log $_log;
    private IO $_ioLogic;
    private LocalFileServerCopier $_localFileServerCopierLogic;
    private FileServerCopier $_fileServerCopierLogic;
    private Database $_databaseLogic;

    private string $copyTo;

    public function __construct(Database              $databaseLogic, IO $ioLogic,
                                LocalFileServerCopier $localFileServerCopierLogic, Log $log)
    {
        $this->_databaseLogic = $databaseLogic;
        $this->_ioLogic = $ioLogic;
        $this->_localFileServerCopierLogic = $localFileServerCopierLogic;
        $this->_log = $log;
    }

    public function copy() : void
    {
        $this->copyTo = $this->_ioLogic->getWhereToCopy();
        $path = $this->getPathToCopy();
        $loadFromLocalLogic = new LocalLoader($this->_ioLogic, $this->_log,
            $this->_ioLogic->getPathToContaoFiles(), $path);
        $files = $loadFromLocalLogic->loadFromLocal();

        $this->createDirectories($files);
        $this->checkForDeletion();
        $this->compareAndCopyFiles($files);
        $this->copyDirectoryToMainDirectoryWithSSHCommand();
    }

    private function getPathToCopy() : string
    {
       if ($this->isToCopyToLocalFileServer()) {
            return $this->_ioLogic->getLocalFileServerConfiguration()->getContaoProdPath();
        }else if ($this->isToCopyToFTPFileServer()) {
            $ftpConnection = new FTPConnection($this->_ioLogic, $this->_log);
            $this->_fileServerCopierLogic = new FileServerCopier($ftpConnection->connect());
            return $this->_ioLogic->getFileServerConfiguration()->getPath();
        }
        $this->couldNotFindCopyTo();
        return "";
    }

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

    private function createDirectory(string $directory) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $this->_localFileServerCopierLogic->createDirectory($directory);
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_fileServerCopierLogic->createDirectory($directory);
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
            $this->_localFileServerCopierLogic->copy($file);
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_fileServerCopierLogic->copy($file);
        }else {
            $this->couldNotFindCopyTo();
        }
    }

    private function checkForUpdate(File $file) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $lastModifiedTime = $this->_localFileServerCopierLogic->getLastModifiedTimeFromFile($file->getProdPath());
            if ($lastModifiedTime < $this->_localFileServerCopierLogic->getLastModifiedTimeFromFile($file->getPath())) {
                $this->_localFileServerCopierLogic->copy($file);
            }
        }else if ($this->isToCopyToFTPFileServer()) {
            $lastModifiedTime = $this->_fileServerCopierLogic->getLastModifiedTimeFromFile($file->getProdPath());
            if ($lastModifiedTime < $this->_fileServerCopierLogic->getLastModifiedTimeFromFile($file->getPath())) {
                $this->_fileServerCopierLogic->update($file);
            }
        }else {
            $this->couldNotFindCopyTo();
        }
    }

    private function checkForDeletion() : void
    {
        $res = $this->_databaseLogic->checkForDeletedFilesInTlLogTable()
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
            $this->_localFileServerCopierLogic->delete($file,
                $this->_ioLogic->getLocalFileServerConfiguration()->getContaoProdPath());
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_fileServerCopierLogic->delete($file, $this->_ioLogic->getFileServerConfiguration()->getPath());
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
        $this->_log->logErrorAndDie("Could not find a valid path to update files");
    }

    private function copyDirectoryToMainDirectoryWithSSHCommand() : void
    {
        if ($this->isToCopyToFTPFileServer()) {
            $config = $this->_ioLogic->getFileServerConfiguration();
            $connection = ssh2_connect($config->getServer(), 22);
            ssh2_auth_password($connection, $config->getUsername(), $config->getPassword());
            $stream = ssh2_exec($connection, "bash -r /html/release-stages.sh");
            stream_set_blocking($stream, true);
            fclose($stream);
        }
    }
}
