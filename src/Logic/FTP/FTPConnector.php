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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use phpseclib3\Net\SFTP;

class FTPConnector {
    private Config $_config;
    private Logger $_logger;

    private string $username;
    private string $password;
    private string $server;
    private int $port;
    private bool $ssl;

    public function __construct(Config $config, Logger $logger)
    {
        $this->_config = $config;
        $this->_logger = $logger;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function setUp(): void
    {
        $config = $this->_config->getFileServerConfiguration();
        $config_ftp = $this->_config->getFTPConfiguration();
        $this->username = $config_ftp->getUsername();
        $this->password = $config_ftp->getPassword();
        $this->server = $config->getServer();
        $this->port = $config_ftp->getPort();
        $this->ssl = $config_ftp->isSsl();
    }

    /**
     * @throws FTPConnection
     */
    public function connect(): AbstractFTPRunner
    {
        if ($this->ssl) {
            $conn = $this->connectToSFTPServer();
            return new SFTPRunner($conn);
        }

        $conn = $this->connectToFTPServer();
        return new FTPRunner($conn);
    }

    /**
     * @throws FTPConnection
     */
    private function connectToSFTPServer(): SFTP
    {
        $sftpConn = new SFTP($this->server, $this->port);
        try {
            $sftpConn->login($this->username, $this->password);
        }catch (\Exception $e) {
            $this->errorMessage("Connection to SFTP Server \"$this->server:$this->port\" failed: $e");
        }finally {
            return $sftpConn;
        }
    }

    /**
     * @return false|resource
     * @throws FTPConnection
     */
    private function connectToFTPServer()
    {
        $ftpConn = ftp_connect($this->server, $this->port);
        if (!$ftpConn) {
            $this->errorMessage("Connection to FTP Server \"$this->server:$this->port\" failed");
        }
        $this->loginFTPServer($ftpConn);
        return $ftpConn;
    }

    /**
     * @throws FTPConnection
     */
    private function loginFTPServer($conn): void
    {
        if (!@ftp_login($conn, $this->username, $this->password)) {
            $this->_logger->error("Username or password is false.");
            throw new FTPConnection("Username or password is false");
        }
    }

    /**
     * @throws FTPConnection
     */
    private function errorMessage(string $message): void
    {
        $this->_logger->error($message);
        throw new FTPConnection($message);
    }

    public function disconnect($conn): void
    {
        if ($this->ssl) {
            $conn->disconnect();
        }else {
            ftp_close($conn);
        }
    }
}
