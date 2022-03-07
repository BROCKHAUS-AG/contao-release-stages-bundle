<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class DNSRecord
{
    private string $alias;
    private string $dns;

    public function __construct(string $alias, string $dns)
    {
        $this->alias = $alias;
        $this->dns = $dns;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getDns(): string
    {
        return $this->dns;
    }
}
