<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Version;

class Version
{
    private int $id;
    private string $kindOfRelease;
    private string $version;

    public function __construct(int $id, string $kindOfRelease, string $version)
    {
        $this->id = $id;
        $this->kindOfRelease = $kindOfRelease;
        $this->version = $version;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getKindOfRelease(): string
    {
        return $this->kindOfRelease;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
