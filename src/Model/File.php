<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model;

class File
{
    private int $lastModifiedTime;
    private string $path;
    private string $prodPath;

    public function __construct(int $lastModifiedTime, string $path, string $prodPath) {
        $this->lastModifiedTime = $lastModifiedTime;
        $this->path = $path;
        $this->prodPath = $prodPath;
    }

    public function getLastModifiedTime() : int
    {
        return $this->lastModifiedTime;
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
