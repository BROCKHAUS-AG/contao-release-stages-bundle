<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper;


use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;

class MapConfig extends Map {

    public function map() : Config
    {
        $contaoPath = $this->json["contaoPath"];

        $databaseMapper = new MapDatabase($this->json["database"]);
        $database = $databaseMapper->map();

        $copyTo = $this->json["copyTo"];

        $fileServerMapper = new MapFileServer($this->json["fileServer"]);
        $fileServer = $fileServerMapper->map();

        $localMapper = new MapLocal($this->json["local"]);
        $local = $localMapper->map();

        $arrayOfDNSRecordsMapper = new MapArrayOfDNSRecords($this->json["dnsRecords"]);
        $dnsRecords = $arrayOfDNSRecordsMapper->map();

        $fileFormats = $this->json["fileFormats"];

        return new Config(

        );
    }
}
