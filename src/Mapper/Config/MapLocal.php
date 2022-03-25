<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Map;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;
use stdClass;

class MapLocal extends Map {

    public function map(stdClass $data) : Local
    {
        return new Local(
          $data->contaoProdPath
        );
    }
}
