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

class CopyToFTPFileServerLogic {
    private $_conn;

    public function __construct($conn)
    {
        $this->_conn = $conn;
    }

    public function createDirectory(string $directory) : void
    {
        if (!$this->checkIfDirectoryExists($directory)) {
            ftp_mkdir($this->_conn, $directory);
            ftp_chmod($this->_conn, 0755, $directory);
        }
    }

    public function getLastModifiedTimeFromFile(string $file) : int
    {
        return ftp_mdtm($this->_conn, $file);
    }

    public function copy(array $file) : void
    {
        if (!$this->checkIfFileExists($file["prodPath"])) {
            if (!@ftp_put($this->_conn, $file["prodPath"], $file["path"], FTP_ASCII)) {
                $errors = error_get_last();
                echo "COPY ERROR: ".$errors['type'];
                echo "<br />\n".$errors['message'];
            }else {
                ftp_chmod($this->_conn, 0644, $file["prodPath"]);
            }
        }
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
        if (!$files) return false;
        return in_array($file, $files);
    }
}
