<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper;

use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;

class MapFileServer extends Map {

    public function map() : FileServer
    {
       return new FileServer(
         $this->json["server"],
         $this->json["port"],
         $this->json["username"],
         $this->json["password"],
         $this->json["ssl_tsl"],
         $this->json["path"]
       );
    }
}
