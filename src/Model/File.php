<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model;

class File
{
    private string $path;
    private string $prodPath;

    public function __construct(string $path, string $prodPath) {
        $this->path = $path;
        $this->prodPath = $prodPath;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getProdPath() : string
    {
        return $this->prodPath;
    }
}
