<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class FileServer
{
    private string $server;
    private string $rootPath;
    private string $contentPath;
    private Ftp $ftp;
    private Ssh $ssh;

    public function __construct(string $server, string $rootPath, Ftp $ftp, Ssh $ssh, string $contentPath)
    {
        $this->server = $server;
        $this->rootPath = $rootPath;
        $this->contentPath = $contentPath;
        $this->ftp = $ftp;
        $this->ssh = $ssh;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getContentPath() : string
    {
        return $this->contentPath;
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
