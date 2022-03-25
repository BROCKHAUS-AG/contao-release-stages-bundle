<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper;

use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;

class MapDatabase extends Map {

    public function map() : Database
    {
        return new Database(
            $this->json["server"],
            $this->json["name"],
            $this->json["port"],
            $this->json["username"],
            $this->json["password"],
            $this->json["ignoredTables"],
            $this->json["testStageDatabaseName"]
        );
    }
}
