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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPConnetion;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;

class FTPConnector {
    private IO $_io;
    private Log $_log;

    private string $username;
    private string $password;
    private string $server;
    private int $port;
    private bool $ssl;

    public function __construct(IO $io, Log $log)
    {
        $this->_io = $io;
        $this->_log = $log;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function setUp(): void
    {
        $config = $this->_io->getFileServerConfiguration();
        $config_ftp = $this->_io->getFTPConfiguration();
        $this->username = $config_ftp->getUsername();
        $this->password = $config_ftp->getPassword();
        $this->server = $config->getServer();
        $this->port = $config_ftp->getPort();
        $this->ssl = $config_ftp->isSsl();
    }

    /**
     * @return false|resource
     * @throws FTPConnetion
     */
    public function connect()
    {
        if ($this->ssl) {
            $conn = $this->connectToSFTPServer();
        }else {
            $conn = $this->connectToFTPServer();
        }
        $this->login($conn);
        return $conn;
    }

    /**
     * @throws FTPConnetion
     */
    private function login($conn): void
    {
        if (!@ftp_login($conn, $this->username, $this->password)) {
            $this->_log->error("Username or password is false.");
            throw new FTPConnetion("Username or password is false");
        }
    }

    /**
     * @return false|resource
     * @throws FTPConnetion
     */
    private function connectToSFTPServer()
    {
        $sftpConn = ftp_ssl_connect($this->server, $this->port);
        if (!$sftpConn) {
            $this->errorMessage("Connection to SFTP Server \"$this->server: $this->port\" failed");
        }
        return $sftpConn;
    }

    /**
     * @return false|resource
     * @throws FTPConnetion
     */
    private function connectToFTPServer()
    {
        $ftpConn = ftp_connect($this->server, $this->port);
        if (!$ftpConn) {
            $this->errorMessage("Connection to FTP Server \"$this->server: $this->port\" failed");
        }
        return $ftpConn;
    }

    /**
     * @throws FTPConnetion
     */
    private function errorMessage(string $message): void
    {
        $this->_log->error($message);
        throw new FTPConnetion($message);
    }

    public function disconnect($conn): void
    {
        ftp_close($conn);
    }
}
