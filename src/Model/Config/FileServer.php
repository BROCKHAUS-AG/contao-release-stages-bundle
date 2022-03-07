<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class FileServer
{
    private string $server;
    private int $port;
    private string $username;
    private string $password;
    private bool $ssl_tsl;
    private string $path;

    public function __construct(string $server, int $port, string $username, string $password, bool $ssl_tsl,
                                string $path)
    {
        $this->server = $server;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->ssl_tsl = $ssl_tsl;
        $this->path = $path;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function isSslTsl(): bool
    {
        return $this->ssl_tsl;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
