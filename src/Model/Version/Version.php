<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Version;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Validation;
use BrockhausAg\ContaoReleaseStagesBundle\Constants;

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
        if(Constants::DEPLOYMENT_SUCCESS != $this->state && Constants::DEPLOYMENT_FAILURE != $this->state &&
            Constants::DEPLOYMENT_PENDING != $this->state && Constants::DEPLOYMENT_OLD_PENDING != $this->state)
        {
            throw new Validation("\"$this->state\" is not a valid state");
        }
    }
}
