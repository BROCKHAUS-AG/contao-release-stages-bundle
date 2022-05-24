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
        $database = $databaseMapper->map($data->database);

        $copyTo = $data->copyTo;

        $fileServerMapper = new FileServerMapper();
        $fileServer = $fileServerMapper->map($data->fileServer);

        $localMapper = new LocalMapper();
        $local = $localMapper->map($data->local);

        $arrayOfDNSRecordsMapper = new DNSRecordCollectionMapper();
        $dnsRecords = $arrayOfDNSRecordsMapper->mapArray($data->dnsRecords);

        $maxSpendTimeWhileCreatingRelease = $data->maxSpendTimeWhileCreatingRelease;

        $fileFormats = $data->fileFormats;

        return new Config($database, $copyTo, $fileServer, $local, $maxSpendTimeWhileCreatingRelease, $dnsRecords,
            $fileFormats);
    }
}
