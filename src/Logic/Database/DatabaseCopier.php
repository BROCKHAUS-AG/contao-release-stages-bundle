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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Database;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\DatabaseExecutionFailure;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Database\TableInformation;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Database\TableInformationCollection;
use Doctrine\DBAL\Exception;

class DatabaseCopier
{
    private Log $_log;
    private Database $_databaseLogic;
    private DatabaseProd $_prodDatabaseLogic;
    private IO $_ioLogic;

    public function __construct(Database $databaseLogic, DatabaseProd $prodDatabaseLogic, IO $ioLogic,
                                Log $log)
    {
        $this->_databaseLogic = $databaseLogic;
        $this->_prodDatabaseLogic = $prodDatabaseLogic;
        $this->_ioLogic = $ioLogic;
        $this->_log = $log;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DatabaseQueryEmptyResult
     * @throws DatabaseExecutionFailure
     */
    public function copy(): void
    {
        $tableInformationCollection = $this->_databaseLogic->getFullTableInformation();
        $this->createTablesIfNotExists($tableInformationCollection);
        $this->createAndExecuteCommandsOnProdDatabase($tableInformationCollection);

        $lastId = $this->_prodDatabaseLogic->getLastIdFromTlLogTable();
        $this->checkForDeleteFromInTlLogTable($lastId);
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DatabaseExecutionFailure
     */
    private function createTablesIfNotExists(TableInformationCollection $tableInformationCollection): void
    {
        for ($x = 0; $x != $tableInformationCollection->getLength(); $x++)
        {
            $this->createTableIfNotExists($tableInformationCollection->getByIndex($x)->getName());
        }
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DatabaseExecutionFailure
     */
    private function createTableIfNotExists(string $table): void
    {
        if (!$this->_prodDatabaseLogic->checkIfTableExists($table)) {
            $tableScheme = $this->_databaseLogic->getTableScheme($table);
            $this->_prodDatabaseLogic->createTable($table, $tableScheme);
        }
    }

    /**
     * @throws Exception
     * @throws DatabaseQueryEmptyResult
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DatabaseExecutionFailure
     */
    private function createAndExecuteCommandsOnProdDatabase(TableInformationCollection $tableInformation): void
    {
        foreach ($tableInformation->get() as $table) {
            $commandsToBeExecuted = $this->createCommandsToBeExecuted($table);
            $this->_prodDatabaseLogic->executeCommands($commandsToBeExecuted);
        }
    }

    /**
     * @throws Exception
     * @throws DatabaseQueryEmptyResult
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function createCommandsToBeExecuted(TableInformation $tableInformation): array
    {
        $values = $this->createColumnWithValuesForCommand($tableInformation);
        return $this->createCommandsWithValueForTable($values, $tableInformation->getName());
    }

    /**
     * @throws Exception
     * @throws DatabaseQueryEmptyResult
     * @throws \Doctrine\DBAL\Driver\Exception
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

    private function changeDNSEntryForProd(string $alias): string
    {
        $dnsRecords = $this->_ioLogic->getDNSRecords();
        for ($x = 0; $x != $dnsRecords->getLength(); $x++) {
            $dnsRecord = $dnsRecords->getByIndex($x);
            if (strcmp($dnsRecord->getAlias(), $alias) == 0) {
                return $dnsRecord->getDns();
            }
        }
        return "";
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
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
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
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
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
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

    /**
     * @throws Exception
     * @throws DatabaseExecutionFailure
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function checkForDeleteFromInTlLogTable(int $lastId): void
    {
        $res = $this->_databaseLogic->getRowsFromTlLogTableWhereIdIsBiggerThanIdAndTextIsLikeDeleteFrom($lastId);

        $deleteStatements = array();
        foreach ($res as $statement) {
            $deleteStatements[] = $statement["text"];
        }
        $this->_prodDatabaseLogic->executeCommands($deleteStatements);
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
