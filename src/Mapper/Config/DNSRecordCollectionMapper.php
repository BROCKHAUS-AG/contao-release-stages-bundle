<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Mapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecordCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecord;
use stdClass;

class DNSRecordCollectionMapper extends Mapper {

    public function map(stdClass $data): DNSRecordCollection
    {
        return $this->_map($data);
    }

    public function mapArray(array $data): DNSRecordCollection
    {
        return $this->_map($data);
    }

    private function _map($data): DNSRecordCollection
    {
        $arrayOfDNSRecords = new DNSRecordCollection();
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
