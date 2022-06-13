<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper;

use stdClass;

abstract class Mapper {
    abstract public function map(stdClass $data) : Object;
}
