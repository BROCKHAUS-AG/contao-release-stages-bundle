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

use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Database\TableInformation;

class MigrationBuilder
{
    private IO $_io;

    public function __construct(string $path)
    {
        $this->_io = new IO($path. Constants::DATABASE_MIGRATION_FILE);
    }

   public function build(string $table, array $tableScheme): void
   {
       $command = $this->buildCreateTableCommand($table, $tableScheme);
       $this->appendCommandToMigrationFile($command);
   }

    /**
     * @throws DatabaseQueryEmptyResult
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function createColumnWithValuesForCommand(TableInformation $tableInformation): array
    {
        $tableSchemes = $this->_prodDatabaseLogic->getTableSchemes($tableInformation->getName());
        $values = array();
        foreach ($tableInformation->getContent() as $column) {
            if (strcmp($tableInformation->getName(), "tl_page") == 0 &&
                strcmp($column["type"], "root") == 0) {
                $column["dns"] = $this->changeDNSEntryForProd($column["alias"]);
            }
            $values[] = $this->createColumnWithValueForCommand($column, $tableSchemes, $tableInformation->getName());
        }
        return $values;
    }



    public function buildAndWriteInsertIntoCommands(): void
    {

    }

    private function changeDNSEntryForProd(string $alias): string
    {
        $dnsRecords = $this->_config->getDNSRecords();
        for ($x = 0; $x != $dnsRecords->getLength(); $x++) {
            $dnsRecord = $dnsRecords->getByIndex($x);
            if (strcmp($dnsRecord->getAlias(), $alias) == 0) {
                return $dnsRecord->getDns();
            }
        }
        return "";
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function createColumnWithValueForCommand(array $column, array $tableSchemes, string $tableName): array
    {
        $index = 0;
        $tableSchemeFields = array();
        $rows = array();
        $columnAndValue = array();
        foreach ($column as $row) {
            $this->createRowsAndColumn($tableSchemes[$index], $rows, $columnAndValue, $tableName,
                $tableSchemeFields, $row, $column["id"]);
            $index++;
        }
        return $this->createReturnValue($rows, $tableSchemeFields, $columnAndValue);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function createRowsAndColumn(array $tableSchemes, array &$rows, array &$columnAndValue,
                                         string $tableName, array &$tableSchemeFields, ?string $row, ?string $id): void
    {
        $type = $tableSchemes["type"];
        if ($this->checkIfColumnTypeIsText($type)) {
            $this->createRowsAndColumnValuesForText($rows, $tableSchemes["field"], $columnAndValue, $row);
        } else if ($this->checkIfColumnIsNullable($tableSchemes["nullable"], $row)) {
            $this->setRowsAndColumnsNull($rows, $tableSchemes["field"], $columnAndValue);
        } else if ($this->checkIfColumnTypeIsBinary($type)) {
            $this->createRowsAndColumnForBinary($tableSchemes["field"], $tableName, $rows, $columnAndValue, $id);
        } else if ($this->checkIfColumnTypeIsBlob($type)) {
            $this->createRowsAndColumnForBlob($rows, $tableSchemes["field"], $columnAndValue, $row);
        } else {
            $this->createRowsAndColumnForNothing($rows, $tableSchemes["field"], $columnAndValue, $row);
        }

        $tableSchemeFields[] = $tableSchemes["field"];
    }

    private function checkIfColumnTypeIsText(string $type): bool
    {
        return strpos($type, "varchar") || strpos($type, "string") || strpos($type, "char") ||
            strcmp($type, "char(1)") == 0 || strpos($type, "text");
    }

    private function createRowsAndColumnValuesForText(array &$rows, string $field, array &$columnAndValue,
                                                      ?string $row): void
    {
        $row = str_replace("'", "\'", $row);
        $rows[] = '\'' . $row . '\'';
        $columnAndValue[] = $field . " = '" . $row . "'";
    }

    private function checkIfColumnIsNullable(string $nullable, ?string $row): bool
    {
        return empty($row) && strcmp($nullable, "YES") == 0;
    }

    private function setRowsAndColumnsNull(array &$rows, string $field, array &$columnAndValue): void
    {
        $rows[] = "NULL";
        $columnAndValue[] = $field . " = NULL";
    }

    private function checkIfColumnTypeIsBinary(string $type): bool
    {
        return strcmp($type, "binary(16)") == 0 || strcmp($type, "varbinary(128)") == 0;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function createRowsAndColumnForBinary(string $field, string $tableName, array &$rows,
                                                  array &$columnAndValue, ?string $id): void
    {
        $req = $this->_databaseLogic->loadHexById($field, $tableName, $id);
        $rows[] = "UNHEX('" . $req[0]["hex(" . $field . ")"] . "')";
        $columnAndValue[] = $field . "= UNHEX('" . $req[0]["hex(" .
            $field . ")"] . "')";
    }

    private function checkIfColumnTypeIsBlob(string $type): bool
    {
        return strcmp($type, "blob") == 0 || strcmp($type, "mediumblob") == 0 ||
            strcmp($type, "longblob") == 0;
    }

    private function createRowsAndColumnForBlob(array &$rows, string $field, array &$columnAndValue, ?string $row): void
    {
        $rows[] = "x'" . bin2hex($row) . "'";
        $columnAndValue[] = $field . " = x'" . bin2hex($row) . "'";
    }

    private function createRowsAndColumnForNothing(array &$rows, string $field, array &$columnAndValue,
                                                   ?string $row): void
    {
        $rows[] = $row;
        $columnAndValue[] = $field . " = " . $row;
    }

    private function createCommandsWithValueForTable(array $values, string $tableName): array
    {
        $commandsToBeExecuted = array();
        foreach ($values as $value) {
            $commandsToBeExecuted[]
                = 'INSERT INTO '. $this->_prodDatabaseLogic->_databaseName. '.'. $tableName. ' ('. $value["columnName"].
                ') VALUES ('. $value["value"]. ') ON DUPLICATE KEY UPDATE '. $value["updateColumnAndValue"]. ';';
        }
        if ($commandsToBeExecuted == null) return array();
        return $commandsToBeExecuted;
    }

    private function createReturnValue(array $rows, array $tableSchemeFields, array $columnAndValue): array
    {
        $value = implode(", ", $rows);
        $columnName = implode(", ", $tableSchemeFields);
        $updateColumnAndValue = implode(", ", $columnAndValue);

        return array(
            "columnName" => $columnName,
            "value" => $value,
            "updateColumnAndValue" => $updateColumnAndValue
        );
    }
}
