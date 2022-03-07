<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class Database
{
    private string $server;
    private string $name;
    private int $port;
    private string $username;
    private string $password;
    private array $ignoredTables;
    private string $testStageDatabaseName;

    public function __construct(string $server, string $name, int $port, string $username, string $password,
                                array $ignoredTables, string $testStageDatabaseName)
    {
        $this->server = $server;
        $this->name = $name;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->ignoredTables = $ignoredTables;
        $this->testStageDatabaseName = $testStageDatabaseName;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function getIgnoredTables(): array
    {
        return $this->ignoredTables;
    }

    public function getTestStageDatabaseName(): string
    {
        return $this->testStageDatabaseName;
    }
}
