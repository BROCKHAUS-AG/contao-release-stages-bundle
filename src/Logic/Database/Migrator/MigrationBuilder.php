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

use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;

class MigrationBuilder
{
    private IO $_io;

    public function __construct(string $path)
    {
        $this->_io = new IO($path. "");
    }

   public function buildAndWriteCreateTableCommandWithTableScheme(string $table, array $tableScheme): void
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
       $command = $command. "PRIMARY KEY(". $primaryKey. "));";
       $this->appendCommandToMigrationFile($command);
   }

    private function setDefaultValueForAttribute(string $scheme): string
    {
        return $scheme == "YES" ? "NULL" : "NOT NULL";
    }

    public function buildAndWriteInsertIntoCommands(): void
    {

    }

    private function appendCommandToMigrationFile(string $command): void
    {
        $this->_io->append($command);
    }
}
