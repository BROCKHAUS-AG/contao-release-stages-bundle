<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class Config
{
    private string $contaoPath;
    private Database $database;
    private string $copyTo;
    private FileServer $fileServer;
    private Local $local;
    private ArrayOfDNSRecords $dnsRecords;
    private array $fileFormats;

    public function __construct(string $contaoPath, Database $database, string $copyTo, FileServer $fileServer,
                                Local $local, ArrayOfDNSRecords $dnsRecords, array $fileFormats)
    {
        $this->contaoPath = $contaoPath;
        $this->database = $database;
        $this->copyTo = $copyTo;
        $this->fileServer = $fileServer;
        $this->local = $local;
        $this->dnsRecords = $dnsRecords;
        $this->fileFormats = $fileFormats;
    }

    public function getContaoPath(): string
    {
        return $this->contaoPath;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getCopyTo(): string
    {
        return $this->copyTo;
    }

    public function getFileServer(): FileServer
    {
        return $this->fileServer;
    }

    public function getLocal(): Local
    {
        return $this->local;
    }

    public function getDnsRecords(): ArrayOfDNSRecords
    {
        return $this->dnsRecords;
    }

    public function getFileFormats(): array
    {
        return $this->fileFormats;
    }
}
