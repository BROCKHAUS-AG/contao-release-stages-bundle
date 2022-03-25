<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;


use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Map;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\ArrayOfDNSRecords;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecord;
use stdClass;

class MapArrayOfDNSRecords extends Map {

    public function map(stdClass $data) : ArrayOfDNSRecords
    {
        return $this->_map($data);
    }

    public function mapArray(array $data) : ArrayOfDNSRecords
    {
        return $this->_map($data);
    }

    private function _map($data) : ArrayOfDNSRecords
    {
        $arrayOfDNSRecords = new ArrayOfDNSRecords();
        foreach ($data as $record) {
            $arrayOfDNSRecords->add(
                new DNSRecord(
                    $record->alias,
                    $record->dns
                )
            );
        }
        return $arrayOfDNSRecords;
    }
}
