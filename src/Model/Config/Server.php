<?php

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

abstract class Server
{
    private int $port;
    private string $username;
    private string $password;

    public function __construct(int $port, string $username, string $password) {
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
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
}
