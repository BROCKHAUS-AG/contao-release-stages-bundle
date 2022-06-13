<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class Ssh extends Server
{
    public function __construct(int $port, string $username, string $password)
    {
        parent::__construct($port, $username, $password);
    }
}
