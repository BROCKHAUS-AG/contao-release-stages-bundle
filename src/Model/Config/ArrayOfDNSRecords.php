<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class ArrayOfDNSRecords
{
    private array $dnsRecords;

    public function __construct()
    {
        $this->dnsRecords = array();
    }

    public function add(DNSRecord $dnsRecord) : void
    {
        $this->dnsRecords[] = $dnsRecord;
    }

    public function get() : array
    {
        return $this->dnsRecords;
    }

    public function getByIndex(int $index) : DNSRecord
    {
        return $this->dnsRecords[$index];
    }

    public function getLength() : int
    {
        return count($this->dnsRecords);
    }
}
