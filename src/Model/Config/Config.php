<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class Config
{
    private Database $databaseProd;
    private Database $databaseTest;
    private FileServer $fileServer;
    private int $maxSpendTimeWhileCreatingRelease;
    private DNSRecordCollection $dnsRecords;

    public function __construct(Database $databaseProd, Database $databaseTest, FileServer $fileServer, int $maxSpendTimeWhileCreatingRelease,
                                DNSRecordCollection $dnsRecords)
    {
        $this->databaseProd = $databaseProd;
        $this->databaseTest = $databaseTest;
        $this->fileServer = $fileServer;
        $this->maxSpendTimeWhileCreatingRelease = $maxSpendTimeWhileCreatingRelease;
        $this->dnsRecords = $dnsRecords;
    }

    public function getProdDatabase(): Database
    {
        return $this->databaseProd;
    }

    public function getTestDatabase(): Database
    {
        return $this->databaseTest;
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
}
