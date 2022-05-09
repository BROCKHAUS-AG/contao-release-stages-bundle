<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Map;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ftp;
use stdClass;

class MapFtp extends Map
{
    public function map(stdClass $data): Ftp
    {
        return new Ftp(
            $data->port,
            $data->username,
            $data->password,
            $data->ssl
        );
    }
}
