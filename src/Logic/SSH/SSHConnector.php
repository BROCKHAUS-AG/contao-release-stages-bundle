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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;

class SSHConnector {
    private IO $_io;
    private Logger $_logger;

    private string $username;
    private string $password;
    private string $server;
    private int $port;

    public function __construct(IO $io, Logger $logger)
    {
        $this->_io = $io;
        $this->_logger = $logger;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function setUp(): void
    {
        $config = $this->_io->getFileServerConfiguration();
        $this->server = $config->getServer();
        $config_ssh = $this->_io->getSSHConfiguration();
        $this->username = $config_ssh->getUsername();
        $this->password = $config_ssh->getPassword();
        $this->port = $config_ssh->getPort();
    }

    /**
     * @return false|resource
     * @throws SSHConnection
     */
    public function connect()
    {
        $connection = ssh2_connect($this->server, $this->port);
        if (!$connection) {
            $message = "Connection to SSH server \"$this->server\" failed";
            $this->_logger->error($message);
            throw new SSHConnection($message);
        }
        if (!ssh2_auth_password($connection, $this->username, $this->password)) {
            $message = "Connection to SSH server \"$this->server\" failed. Username or password is false";
            $this->_logger->error($message);
            throw new SSHConnection($message);
        }
        return $connection;
    }

    public function disconnect($stream): void
    {
        fclose($stream);
    }
}
