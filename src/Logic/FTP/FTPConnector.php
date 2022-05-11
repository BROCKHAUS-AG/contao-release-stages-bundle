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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP;

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;

class FTPConnector {
    private IO $_io;
    private Log $_log;

    private string $username;
    private string $password;
    private string $server;
    private int $port;
    private bool $ssl_tsl;

    public function __construct(IO $io, Log $log)
    {
        $this->_io = $io;
        $this->_log = $log;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function setUpFTPConfig(): void
    {
        $config = $this->_io->getFileServerConfiguration();
        $config_ftp = $this->_io->getFTPConfiguration();
        $this->username = $config_ftp->getUsername();
        $this->password = $config_ftp->getPassword();
        $this->server = $config->getServer();
        $this->port = $config_ftp->getPort();
        $this->ssl_tsl = $config_ftp->isSsl();
    }

    public function connect(): bool
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
            $this->_log->logErrorAndDie("Username or password is false.");
        }
    }

    private function connectToSFTPServer(): bool
    {
        $sftpConn = ftp_ssl_connect($this->server, $this->port)
            or $this->_log->logErrorAndDie("Connection to SFTP Server \"". $this->server. "\" failed");
        return $sftpConn;
    }

    private function connectToFTPServer(): bool
    {
        $ftpConn = ftp_connect($this->server, $this->port)
            or $this->_log->logErrorAndDie("Connection to FTP Server \"". $this->server. "\" failed");
        return $ftpConn;
    }

    public function disconnect($conn) : void
    {
        ftp_close($conn);
    }
}
