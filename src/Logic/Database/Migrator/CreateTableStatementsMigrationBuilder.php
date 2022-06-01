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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\CreateTableMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseProd;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Database\TableInformationCollection;
use Doctrine\DBAL\Exception;
use Throwable;

class CreateTableStatementsMigrationBuilder
{
    private Database $_database;
    private DatabaseProd $_databaseProd;

    public function __construct(Database $database, DatabaseProd $databaseProd)
    {
        $this->_database = $database;
        $this->_databaseProd = $databaseProd;
    }

    /**
     * @throws CreateTableMigrationBuilder
     */
    public function build(): array
    {
        try {
            $tableInformationCollection = $this->_database->getFullTableInformation();
            return $this->buildCreateTableCommandsIfNotExists($tableInformationCollection);
        }catch (Throwable $e) {
            throw new CreateTableMigrationBuilder("Couldn't build create table statements: $e");
        }
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    private function buildCreateTableCommandsIfNotExists(TableInformationCollection $tableInformationCollection): array
    {
        $commands = array();
        for ($x = 0; $x != $tableInformationCollection->getLength(); $x++)
        {
            $command = $this->buildCreateTableCommandIfNotExists($tableInformationCollection->getByIndex($x)->getName());
            if ($command != null) {
                $commands[] = $command;
            }
        }
        return $commands;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    private function buildCreateTableCommandIfNotExists(string $table): ?string
    {
        if (!$this->_databaseProd->checkIfTableExists($table)) {
            $tableScheme = $this->_database->getTableScheme($table);
            $command = $this->buildCreateTableCommand($table, $tableScheme);
        }
        return $command ?? null;
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
