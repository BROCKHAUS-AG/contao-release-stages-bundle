<?php

declare(strict_types=1);

/*
 * This file is part of contao-release-stages-bundle.
 *
 * (c) BROCKHAUS AG 2022 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-release-stages-bundle
 */

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Migrator;

class CreateTableCommandsMigrationBuilder
{
    public function build(string $table, array $tableScheme): string
    {
        return $this->buildCreateTableCommand($table, $tableScheme);
    }

    private function buildCreateTableCommand(string $table, array $tableScheme): string
    {
        $command = "CREATE TABLE ". $table. "(";
        $primaryKey = "";
        for ($x = 0; $x != count($tableScheme); $x++)
        {
            $scheme = $tableScheme[$x];
            $defaultValue = $this->setDefaultValueForAttribute($scheme["Null"]);
            $command = $command. $scheme["Field"]. " ". $scheme["Type"]. " ".
                $defaultValue. ", ";
            if (isset($scheme["Key"]) && $scheme["Key"] == "PRI") {
                $primaryKey = $scheme["Field"];
            }
        }
        return $command. "PRIMARY KEY(". $primaryKey. "));";
    }

    private function setDefaultValueForAttribute(string $scheme): string
    {
        return $scheme == "YES" ? "NULL" : "NOT NULL";
    }
}
