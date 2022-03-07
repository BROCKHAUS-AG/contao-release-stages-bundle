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
use BrockhausAg\ContaoReleaseStagesBundle\Model\ArrayOfFile;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use Contao\Backend;
use Psr\Log\LoggerInterface;

DEFINE("COPY_TO_LOCAL", "local");
DEFINE("COPY_TO_FILE_SERVER", "fileServer");

class CopyToFileServerLogic extends Backend {
    private LoggerInterface $logger;
    private IOLogic $_ioLogic;
    private CopyToLocalFileServerLogic $_copyToLocalFileServerLogic;
    private CopyToFTPFileServerLogic $_copyToFTPFileServerLogic;
    private DatabaseLogic $_databaseLogic;

    private string $copyTo;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->_ioLogic = new IOLogic($logger);
        $this->_copyToLocalFileServerLogic = new CopyToLocalFileServerLogic();
        $this->_databaseLogic = new DatabaseLogic($logger);

        $logger->info("Hello from release stages");
    }

    public function copyToFileServer() : void
    {
        $this->copyTo = $this->_ioLogic->checkWhereToCopy();
        $path = $this->getPathToCopy();
        $loadFromLocalLogic = new LoadFromLocalLogic($this->logger, $this->_ioLogic->loadPathToContaoFiles(), $path);
        $files = $loadFromLocalLogic->loadFromLocal();

        $this->createDirectories($files);
        $this->checkForDeletion();
        $this->compareAndCopyFiles($files);
        $this->copyDirectoryToMainDirectoryWithSSHCommand();
    }

    private function getPathToCopy() : string
    {
       if ($this->isToCopyToLocalFileServer()) {
            return $this->_ioLogic->loadLocalFileServerConfiguration()->getContaoProdPath();
        }else if ($this->isToCopyToFTPFileServer()) {
            $ftpConnection = new FTPConnection($this->logger);
            $this->_copyToFTPFileServerLogic = new CopyToFTPFileServerLogic($ftpConnection->connect());
            return $this->_ioLogic->loadFileServerConfiguration()->getPath();
        }
        $this->couldNotFindCopyTo();
        return "";
    }

    private function createDirectories(ArrayOfFile $files) : void
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

    private function compareAndCopyFiles(ArrayOfFile $files) : void
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
            $this->_copyToLocalFileServerLogic->copy($file);
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_copyToFTPFileServerLogic->copy($file);
        }else {
            $this->couldNotFindCopyTo();
        }
    }

    private function checkForUpdate(File $file) : void
    {
        if ($this->isToCopyToLocalFileServer()) {
            $lastModifiedTime = $this->_copyToLocalFileServerLogic->getLastModifiedTimeFromFile($file->getProdPath());
            if ($lastModifiedTime < $this->_copyToLocalFileServerLogic->getLastModifiedTimeFromFile($file->getPath())) {
                $this->_copyToLocalFileServerLogic->copy($file);
            }
        }else if ($this->isToCopyToFTPFileServer()) {
            $lastModifiedTime = $this->_copyToFTPFileServerLogic->getLastModifiedTimeFromFile($file->getProdPath());
            if ($lastModifiedTime < $this->_copyToFTPFileServerLogic->getLastModifiedTimeFromFile($file->getPath())) {
                $this->_copyToFTPFileServerLogic->update($file);
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
            $this->_copyToLocalFileServerLogic->delete($file,
                $this->_ioLogic->loadLocalFileServerConfiguration()->getContaoProdPath());
        }else if ($this->isToCopyToFTPFileServer()) {
            $this->_copyToFTPFileServerLogic->delete($file, $this->_ioLogic->loadFileServerConfiguration()->getPath());
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

    private function copyDirectoryToMainDirectoryWithSSHCommand() : void
    {
        if ($this->isToCopyToFTPFileServer()) {
            $config = $this->_ioLogic->loadFileServerConfiguration();
            $connection = ssh2_connect($config->getServer(), 22);
            ssh2_auth_password($connection, $config->getUsername(), $config->getPassword());
            $stream = ssh2_exec($connection, "bash -r /html/release-stages.sh");
            stream_set_blocking($stream, true);
            fclose($stream);
        }
    }
}
