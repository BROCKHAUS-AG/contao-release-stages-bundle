<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class Config
{
    private Database $database;
    private FileServer $fileServer;
    private int $maxSpendTimeWhileCreatingRelease;
    private DNSRecordCollection $dnsRecords;
    private array $fileFormats;

    public function __construct(Database $database, FileServer $fileServer, int $maxSpendTimeWhileCreatingRelease,
                                DNSRecordCollection $dnsRecords, array $fileFormats)
    {
        $this->database = $database;
        $this->fileServer = $fileServer;
        $this->maxSpendTimeWhileCreatingRelease = $maxSpendTimeWhileCreatingRelease;
        $this->dnsRecords = $dnsRecords;
        $this->fileFormats = $fileFormats;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getFileServer(): FileServer
    {
        return $this->fileServer;
    }

    public function getMaxSpendTimeWhileCreatingRelease(): int
    {
        return $this->maxSpendTimeWhileCreatingRelease;
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
