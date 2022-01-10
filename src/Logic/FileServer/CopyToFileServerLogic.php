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
use Contao\Backend;

DEFINE("PATH", "/var/www/html/contao/files/");
DEFINE("PATH_PROD", "/var/www/html/contao/filesProd/");

class CopyToFileServerLogic extends Backend {
    private LoadFromLocalLogic $_loadFromLocalLogic;
    private DatabaseLogic $_databaseLogic;

    public function __construct()
    {
        $this->_loadFromLocalLogic = new LoadFromLocalLogic(PATH, PATH_PROD);
        $this->_databaseLogic = new DatabaseLogic();
    }

    public function copyToFileServer() : void
    {
        $files = $this->_loadFromLocalLogic->loadFromLocal();
        $this->createDirectories($files);
        $this->compareAndCopyFiles($files);
        die;
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
        if (!@mkdir($directory)) {
            $error = error_get_last();
            die("mkdir error: ". $error['message']);
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
           $this->copy($file);
        }

        $this->checkForDeletion();
    }

    private function checkForUpdate(array $file) : void
    {
        if (filemtime($file["prodPath"]) < filemtime($file["path"])) {
            $this->copy($file);
        }
    }

    private function copy(array $file) : void
    {
        if (!copy($file["path"], $file["prodPath"])) {
            $errors = error_get_last();
            echo "COPY ERROR: ".$errors['type'];
            echo "<br />\n".$errors['message'];
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
        $file = str_replace("files", "", $file);
        $file = PATH_PROD. $file;
        if (file_exists($file)) {
            if (!unlink($file)) {
                $error = error_get_last();
                die("Fehler beim Löschen der Datei: \"". $file. "\"</br>rm error: ". $error['message']);
            }
        }
    }
}
