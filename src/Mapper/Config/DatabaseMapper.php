<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Mapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use stdClass;

class DatabaseMapper extends Mapper {

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
