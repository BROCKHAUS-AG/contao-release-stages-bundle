<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class FileServer
{
    private string $server;
    private string $path;
    private Ftp $ftp;
    private Ssh $ssh;

    public function __construct(string $server, string $path, Ftp $ftp, Ssh $ssh)
    {
        $this->server = $server;
        $this->path = $path;
        $this->ftp = $ftp;
        $this->ssh = $ssh;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFtp(): Ftp
    {
        return $this->ftp;
    }

    public function getSsh(): Ssh
    {
        return $this->ssh;
    }
}
