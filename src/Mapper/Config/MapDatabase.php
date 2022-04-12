<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Map;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use stdClass;

class MapDatabase extends Map {

    public function map(stdClass $data) : Database
    {
        return new Database(
            $data->server,
            $data->name,
            $data->port,
            $data->username,
            $data->password,
            $data->ignoredTables
        );
    }
}
