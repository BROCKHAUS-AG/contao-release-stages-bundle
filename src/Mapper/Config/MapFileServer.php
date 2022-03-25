<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Map;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use stdClass;

class MapFileServer extends Map {

    public function map(stdClass $data) : FileServer
    {
       return new FileServer(
         $data->server,
         $data->port,
         $data->username,
         $data->password,
         $data->ssl_tsl,
         $data->path
       );
    }
}
