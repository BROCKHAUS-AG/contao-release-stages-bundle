<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Config;

class Local
{
    private string $contaoProdPath;

    public function __construct(string $contaoProdPath)
    {
        $this->contaoProdPath = $contaoProdPath;
    }

    public function getContaoProdPath(): string
    {
        return $this->contaoProdPath;
    }
}
