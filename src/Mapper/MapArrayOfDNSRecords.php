<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper;


use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\ArrayOfDNSRecords;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecord;

class MapArrayOfDNSRecords extends Map {

    public function map() : ArrayOfDNSRecords
    {
        $arrayOfDNSRecords = new ArrayOfDNSRecords();
        foreach ($this->json as $record) {
            $arrayOfDNSRecords->add(
                new DNSRecord(
                    $record["alias"],
                    $record["dns"]
                )
            );
        }
        return $arrayOfDNSRecords;
    }
}
