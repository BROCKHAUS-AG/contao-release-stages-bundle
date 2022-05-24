<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config;

use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Mapper;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;
use stdClass;

class LocalMapper extends Mapper {

    public function map(stdClass $data) : Local
    {
        return new Local(
          $data->contaoProdPath
        );
    }
}
