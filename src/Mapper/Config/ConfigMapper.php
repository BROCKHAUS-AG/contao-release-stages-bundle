<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Mapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;
use stdClass;

class ConfigMapper extends Mapper {
    public function map(stdClass $data) : Config
    {
        $databaseMapper = new DatabaseMapper();
        $databaseProd = $databaseMapper->map($data->databaseProd);
        $databaseTest = $databaseMapper->map($data->databaseTest);

        $fileServerMapper = new FileServerMapper();
        $fileServer = $fileServerMapper->map($data->fileServer);

        $arrayOfDNSRecordsMapper = new DNSRecordCollectionMapper();
        $dnsRecords = $arrayOfDNSRecordsMapper->mapArray($data->dnsRecords);

        $maxSpendTimeWhileCreatingRelease = $data->maxSpendTimeWhileCreatingRelease;

        return new Config($databaseProd, $databaseTest, $fileServer, $maxSpendTimeWhileCreatingRelease, $dnsRecords);
    }
}
