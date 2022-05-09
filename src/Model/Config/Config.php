<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class Config
{
    private Database $database;
    private string $copyTo;
    private FileServer $fileServer;
    private Local $local;
    private DNSRecordCollection $dnsRecords;
    private array $fileFormats;

    public function __construct(Database $database, string $copyTo, FileServer $fileServer, Local $local,
                                DNSRecordCollection $dnsRecords, array $fileFormats)
    {
        $this->database = $database;
        $this->copyTo = $copyTo;
        $this->fileServer = $fileServer;
        $this->local = $local;
        $this->dnsRecords = $dnsRecords;
        $this->fileFormats = $fileFormats;
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

    public function getDnsRecords(): DNSRecordCollection
    {
        return $this->dnsRecords;
    }

    public function getFileFormats(): array
    {
        return $this->fileFormats;
    }
}
