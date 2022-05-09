<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Map;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ssh;
use stdClass;

class MapSsh extends Map
{
    public function map(stdClass $data): Ssh
    {
        return new Ssh(
            $data->port,
            $data->username,
            $data->password
        );
    }
}
