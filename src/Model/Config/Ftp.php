<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class Ftp extends Server
{
    private bool $ssl;

    public function __construct(int $port, string $username, string $password, bool $ssl)
    {
        parent::__construct($port, $username, $password);
        $this->ssl = $ssl;
    }

    public function isSsl(): bool
    {
        return $this->ssl;
    }
}
