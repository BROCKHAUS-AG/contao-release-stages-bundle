<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Map;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use stdClass;

class MapFileServer extends Map
{
    public function map(stdClass $data): FileServer
    {
        $ftpMapper = new MapFtp();
        $ftp = $ftpMapper->map($data->ftp);

        $sshMapper = new MapSsh();
        $ssh = $sshMapper->map($data->ssh);

       return new FileServer($data->server, $data->path, $ftp, $ssh);
    }
}
