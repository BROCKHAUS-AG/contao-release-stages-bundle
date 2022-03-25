<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Map;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;
use stdClass;

class MapConfig extends Map {
    public function map(stdClass $data) : Config
    {
        $databaseMapper = new MapDatabase();
        $database = $databaseMapper->map($data->database);

        $copyTo = $data->copyTo;

        $fileServerMapper = new MapFileServer();
        $fileServer = $fileServerMapper->map($data->fileServer);

        $localMapper = new MapLocal();
        $local = $localMapper->map($data->local);

        $arrayOfDNSRecordsMapper = new MapArrayOfDNSRecords();
        $dnsRecords = $arrayOfDNSRecordsMapper->mapArray($data->dnsRecords);

        $fileFormats = $data->fileFormats;

        return new Config($database, $copyTo, $fileServer, $local, $dnsRecords, $fileFormats);
    }
}
