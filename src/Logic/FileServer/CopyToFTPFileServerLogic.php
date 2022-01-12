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
        ftp_mkdir($this->_conn, $directory);
    }

    public function getLastModifiedTimeFromFile(string $file) : int
    {
        return ftp_mdtm($this->_conn, $file);
    }

    public function copy(array $file) : void
    {
        if (!@ftp_put($this->_conn, $file["prodPath"], $file["path"], FTP_ASCII)) {
            $errors = error_get_last();
            echo "COPY ERROR: ".$errors['type'];
            echo "<br />\n".$errors['message'];
        }
    }

    public function delete(string $file) : void
    {
        if (file_exists($file)) {
            if (!@ftp_delete($this->_conn, $file)) {
                $error = error_get_last();
                die("Fehler beim Löschen der Datei: \"". $file. "\"</br>rm error: ". $error['message']);
            }
        }
    }
}
