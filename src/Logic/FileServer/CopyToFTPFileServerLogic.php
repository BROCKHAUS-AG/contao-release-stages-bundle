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

use BrockhausAg\ContaoReleaseStagesBundle\Model\File;

class CopyToFTPFileServerLogic {
    private  $_conn;

    public function __construct($conn)
    {
        $this->_conn = $conn;
    }

    public function createDirectory(string $directory) : void
    {
        if (!$this->checkIfDirectoryExists($directory)) {
            $this->mkdir($directory);
            $this->repairDirectoryPermission($directory);
        }
    }

    public function getLastModifiedTimeFromFile(string $file) : int
    {
        return ftp_mdtm($this->_conn, $file);
    }

    public function copy(File $file) : void
    {
        $prodPath = $file->getProdPath();
        $path = $file->getPath();
        if ($this->checkIfFileExists($prodPath)) {
            return;
        }
        $this->put($prodPath, $path);
    }

    public function update(File $file) : void
    {
        $this->copy($file);
    }

    public function delete(string $file, string $path) : void
    {
        $file = $path. $file;
        if ($this->checkIfFileExists($file)) {
            ftp_delete($this->_conn, $file);
        }
    }

    private function checkIfDirectoryExists(string $path) : bool
    {
        if (@ftp_chdir($this->_conn, $path)) {
            return true;
        }
        return false;
    }

    private function checkIfFileExists(string $file) : bool
    {
        $fileName = substr($file, strrpos($file, '/') + 1);
        $path = str_replace($fileName, "", $file);
        $files = ftp_nlist($this->_conn, $path);
        if (!$files) {
            return false;
        }
        return in_array($file, $files);
    }

    private function mkdir(string $directory) : void
    {
        ftp_mkdir($this->_conn, $directory);
    }

    private function repairDirectoryPermission(string $directory) : void
    {
        $this->changePermission($directory, 0755);
    }

    private function put(string $serverPath, string $path) : void
    {
        if (@ftp_put($this->_conn, $serverPath, $path, FTP_ASCII)) {
            $this->repairPermission($serverPath);
        }else {
            $this->handleFileCopyError();
        }
    }

    private function repairPermission(string $prodPath) : void
    {
        $this->changePermission($prodPath, 0644);
    }

    private function handleFileCopyError() : void
    {
        $errors = error_get_last();
        echo "COPY ERROR: " . $errors['type'];
        echo "<br />\n" . $errors['message'];
    }

    private function changePermission(string $directory, int $permission) : void
    {
        ftp_chmod($this->_conn, $permission, $directory);
    }
}
