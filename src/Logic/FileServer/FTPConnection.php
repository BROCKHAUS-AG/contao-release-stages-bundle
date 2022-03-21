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
use Psr\Log\LoggerInterface;

class FTPConnection {
    private LoggerInterface $logger;

    private string $username;
    private string $password;
    private string $server;
    private int $port;
    private bool $ssl_tsl;

    public function __construct(IOLogic $ioLogic, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $config = $ioLogic->loadFileServerConfiguration();
        $this->username = $config->getUsername();
        $this->password = $config->getPassword();
        $this->server = $config->getServer();
        $this->port = $config->getPort();
        $this->ssl_tsl = $config->isSslTsl();
    }

    public function connect()
    {
        if ($this->ssl_tsl) {
            $conn = $this->connectToSFTPServer();
        }else {
            $conn = $this->connectToFTPServer();
        }
        $this->login($conn);
        return $conn;
    }

    private function login($conn) : void
    {
        if (!@ftp_login($conn, $this->username, $this->password)) {
            die("Username oder Passwort ist falsch.");
        }
    }

    private function connectToSFTPServer()
    {
        $sftpConn = ftp_ssl_connect($this->server, $this->port)
            or die("Verbindung zum SFTP Server \"". $this->server. "\" fehlgeschlagen");
        return $sftpConn;
    }

    private function connectToFTPServer()
    {
        $ftpConn = ftp_connect($this->server, $this->port)
            or die("Verbindung zum FTP Server \"". $this->server. "\" fehlgeschlagen");
        return $ftpConn;
    }

    public function disconnect($conn) : void
    {
        ftp_close($conn);
    }
}
