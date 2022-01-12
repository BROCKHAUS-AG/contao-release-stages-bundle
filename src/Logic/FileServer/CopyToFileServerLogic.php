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

use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IOLogic;
use Contao\Backend;

DEFINE("PATH", "/var/www/html/contao/files/");

DEFINE("COPY_TO_LOCAL", "local");
DEFINE("COPY_TO_FILE_SERVER", "fileServer");

class CopyToFileServerLogic extends Backend {
    private IOLogic $_ioLogic;
    private CopyToLocalFileServerLogic $_copyToLocalFileServerLogic;
    private CopyToFTPFileServerLogic $_copyToFTPFileServerLogic;
    private DatabaseLogic $_databaseLogic;

    private string $copyTo;

    public function __construct()
    {
        $this->_ioLogic = new IOLogic();
        $this->_copyToLocalFileServerLogic = new CopyToLocalFileServerLogic();
        $this->_copyToFTPFileServerLogic = new CopyToFTPFileServerLogic();
        $this->_databaseLogic = new DatabaseLogic();
    }

    public function copyToFileServer() : void
    {
        $this->copyTo = $this->_ioLogic->checkWhereToCopy();
        $path = $this->getPathToCopy();
        $loadFromLocalLogic = new LoadFromLocalLogic(PATH, $path);
        $files = $loadFromLocalLogic->loadFromLocal();
        $this->createDirectories($files);
        $this->compareAndCopyFiles($files);
    }

    private function getPathToCopy() : string
    {
        if ($this->isToCopyToLocalFileServer()) {
            return $this->_ioLogic->loadLocalFileServerConfiguration()["contaoProdPath"];
        }else if ($this->isToCopyToFTPFileServer()) {
            return PATH;
        }
        $this->couldNotFindCopyTo();
        return "";
    }

    private function createDirectories(array $files) : void
    {
        foreach ($files as $file)
        {
            $directories = $this->getDirectoriesFromFilePath($file["prodPath"]);
            foreach ($directories as $directory)
            {
                if (!is_dir($directory)) {
                    $this->createDirectory($directory);
                }
            }
        }
    }

    private function createDirectory(string $directory) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $this->_copyToLocalFileServerLogic->createDirectory($directory);
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_copyToFTPFileServerLogic->createDirectory($directory);
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

    private function compareAndCopyFiles(array $files) : void
    {
        foreach ($files as $file)
        {
            $this->compareAndCopyFile($file);
        }
    }

    private function compareAndCopyFile(array $file) : void
    {
        if (file_exists($file["prodPath"])) {
            $this->checkForUpdate($file);
        }else {
            if ($this->isToCopyToLocalFileServer()) {
                $this->_copyToLocalFileServerLogic->copy($file);
            }else if ($this->isToCopyToFTPFileServer()) {
                $this->_copyToFTPFileServerLogic->copy($file);
            }else {
                $this->couldNotFindCopyTo();
            }
        }

        $this->checkForDeletion();
    }

    private function checkForUpdate(array $file) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $lastModifiedTime = $this->_copyToLocalFileServerLogic->getLastModifiedTimeFromFile($file["prodPath"]);
            if ($lastModifiedTime < $this->_copyToLocalFileServerLogic->getLastModifiedTimeFromFile($file["path"])) {
                $this->_copyToLocalFileServerLogic->copy($file);
            }
        }else if ($this->isToCopyToFTPFileServer()) {
            $lastModifiedTime = $this->_copyToFTPFileServerLogic->getLastModifiedTimeFromFile($file["prodPath"]);
            if ($lastModifiedTime < filemtime($file["path"])) {
                $this->_copyToFTPFileServerLogic->copy($file);
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
            $this->deleteFile($file["text"]);
        }
    }

    private function deleteFile(string $file) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $this->_copyToLocalFileServerLogic->delete($file,
                $this->_ioLogic->loadLocalFileServerConfiguration()["contaoProdPath"]);
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_copyToFTPFileServerLogic->delete($file);
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
        die("Es konnte kein valider Pfad gefunden werden, um Dateien zu aktualisieren!");
    }
}
