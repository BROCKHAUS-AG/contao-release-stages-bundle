<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Database;

class TableInformation
{
    private string $name;
    private array $content;

    public function __construct(string $path, array $prodPath) {
        $this->name = $path;
        $this->content = $prodPath;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContent(): array
    {
        return $this->content;
    }
}
