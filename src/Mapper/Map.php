<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Mapper;

abstract class Map {
    protected array $json;

    public function __construct(array $json)
    {
        $this->json = $json;
    }

    abstract public function map() : Object;
}
