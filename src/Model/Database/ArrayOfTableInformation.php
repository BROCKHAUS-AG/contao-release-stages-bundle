<?php

declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Model\Database;

class ArrayOfTableInformation
{
    private array $tableInformation;

    public function __construct()
    {
        $this->tableInformation = array();
    }

    public function add(TableInformation $file): void
    {
        $this->tableInformation[] = $file;
    }

    public function get(): array
    {
        return $this->tableInformation;
    }

    public function getByIndex(int $index): TableInformation
    {
        return $this->tableInformation[$index];
    }
}
