<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper;

use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;

class MapLocal extends Map {

    public function map() : Local
    {
        return new Local(
          $this->json["contaoProdPath"]
        );
    }
}
