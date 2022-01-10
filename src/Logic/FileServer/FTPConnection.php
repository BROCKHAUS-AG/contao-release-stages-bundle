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

use BrockhausAg\ContaoReleaseStagesBundle\Logic\IOLogic;

class FTPConnection {
    private array $_config;

    public function __construct()
    {
        $ioLogic = new IOLogic();
        $this->_config = $ioLogic->loadFileServerConfiguration();
    }

    public function connect()
    {
        if ($this->_config["ssl_tsl"]) {
            $conn = $this->connectToSFTPServer();
        }else {
            $conn = $this->connectToFTPServer();
        }
        $this->login($conn);
        return $conn;
    }

    private function login($conn) : void
    {
        if (!@ftp_login($conn, $this->_config["username"], $this->_config["password"])) {
            die("Username oder Passwort ist falsch.");
        }
    }

    private function connectToSFTPServer()
    {
        $sftpConn = ftp_ssl_connect($this->_config["server"], $this->_config["port"])
            or die("Verbindung zum SFTP Server \"". $this->_config["server"]. "\" fehlgeschlagen");
        return $sftpConn;
    }

    private function connectToFTPServer()
    {
        $ftpConn = ftp_connect($this->_config["server"], $this->_config["port"])
            or die("Verbindung zum FTP Server \"". $this->_config["server"]. "\" fehlgeschlagen");
        return $ftpConn;
    }

    public function disconnect($conn) : void
    {
        ftp_close($conn);
    }
}
