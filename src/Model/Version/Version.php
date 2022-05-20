<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Version;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Validation;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemVariables;
use phpDocumentor\Reflection\Types\This;

class Version
{
    private int $id;
    private string $kindOfRelease;
    private string $version;
    private string $state;

    /**
     * @throws Validation
     */
    public function __construct(int $id, string $kindOfRelease, string $version, string $state)
    {
        $this->id = $id;
        $this->kindOfRelease = $kindOfRelease;
        $this->version = $version;
        $this->state = $state;
        $this->checkState();
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

    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @throws Validation
     */
    private function checkState(): void
    {
        if(SystemVariables::STATE_SUCCESS != $this->state && SystemVariables::STATE_FAILURE != $this->state &&
            SystemVariables::STATE_PENDING != $this->state && SystemVariables::STATE_OLD_PENDING != $this->state)
        {
            throw new Validation("\"$this->state\" is not a valid state");
        }
    }
}
