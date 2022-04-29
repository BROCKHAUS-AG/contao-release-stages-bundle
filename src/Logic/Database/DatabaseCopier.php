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
     */
    public function copy(): void
    {
        $tableInformationCollection = $this->_databaseLogic->getFullTableInformation();
        $this->createTablesIfNotExists($tableInformationCollection);
        $this->createAndExecuteCommandsOnProdDatabase($tableInformationCollection);

        $lastId = $this->_prodDatabaseLogic->getLastIdFromTlLogTable();
        die($lastId);
        $this->checkForDeleteFromInTlLogTable($lastId);
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
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
     */
    private function createAndExecuteCommandsOnProdDatabase(TableInformationCollection $tableInformation): void
    {
        foreach ($tableInformation->get() as $table) {
            echo "before creating command";
            $commandsToBeExecuted = $this->createCommandsToBeExecuted($table);
            echo "after command for table: ". $table. " was created";
            $this->_prodDatabaseLogic->runSqlCommandsOnProdDatabase($commandsToBeExecuted);
        }
    }

    /**
     * @throws Exception
     * @throws DatabaseQueryEmptyResult
     */
    private function createCommandsToBeExecuted(TableInformation $tableInformation): array
    {
        $values = $this->createColumnWithValuesForCommand($tableInformation);
        return $this->createCommandsWithValueForTable($values, $tableInformation->getName());
    }

    /**
     * @throws Exception
     * @throws DatabaseQueryEmptyResult
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
        echo "test1";
        var_dump($values);
        die;
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
     */
    private function createColumnWithValueForCommand(array $column, array $tableSchemes, string $tableName): array
    {
        $index = 0;
        $tableSchemeFields = array();
        $rows = array();
        $columnAndValue = array();
        foreach ($column as $row) {
            if (strpos($tableSchemes[$index]["type"], "varchar") ||
                strpos($tableSchemes[$index]["type"], "string") ||
                strpos($tableSchemes[$index]["type"], "char") ||
                strcmp($tableSchemes[$index]["type"], "char(1)") == 0 ||
                strpos($tableSchemes[$index]["type"], "text")) {
                $row = str_replace("'", "\'", $row);
                $rows[] = '\''. $row. '\'';
                $columnAndValue[] = $tableSchemes[$index]["field"]. " = '". $row. "'";
            }else if (empty($row) && strcmp($tableSchemes[$index]["nullable"], "YES") == 0) {
                $rows[] = "NULL";
                $columnAndValue[] = $tableSchemes[$index]["field"]. " = NULL";
            }else if (strcmp($tableSchemes[$index]["type"], "binary(16)") == 0 ||
                strcmp($tableSchemes[$index]["type"], "varbinary(128)") == 0) {
                $req = $this->_databaseLogic->loadHexById($tableSchemes[$index]["field"], $tableName, $column["id"])
                    ->fetchAllAssoc();
                $rows[] = "UNHEX('". $req[0]["hex(". $tableSchemes[$index]["field"]. ")"]. "')";
                $columnAndValue[] = $tableSchemes[$index]["field"]. "= UNHEX('". $req[0]["hex(".
                    $tableSchemes[$index]["field"]. ")"]. "')";
            }else if (strcmp($tableSchemes[$index]["type"], "blob") == 0 ||
                strcmp($tableSchemes[$index]["type"], "mediumblob") == 0 ||
                strcmp($tableSchemes[$index]["type"], "longblob") == 0) {
                $rows[] = "x'". bin2hex($row). "'";
                $columnAndValue[] = $tableSchemes[$index]["field"]. " = x'". bin2hex($row). "'";
            }else {
                $rows[] = $row;
                $columnAndValue[] = $tableSchemes[$index]["field"]. " = ". $row;
            }

            $tableSchemeFields[] = $tableSchemes[$index]["field"];
            $index++;
        }
        return $this->createReturnValue($rows, $tableSchemeFields, $columnAndValue);
    }

    private function createCommandsWithValueForTable(array $values, string $tableName): array
    {
        $commandsToBeExecuted = array();
        foreach ($values as $value) {
            $commandsToBeExecuted[]
                = 'INSERT INTO '. $this->_prodDatabaseLogic->_prodDatabaseName. '.'. $tableName. ' ('. $value["columnName"].
                ') VALUES ('. $value["value"]. ') ON DUPLICATE KEY UPDATE '. $value["updateColumnAndValue"]. ';';
        }
        if ($commandsToBeExecuted == null) return array();
        return $commandsToBeExecuted;
    }

    /**
     * @throws Exception
     */
    private function checkForDeleteFromInTlLogTable(int $lastId): void
    {
        $whereStatement = "id > ". $lastId. " AND text LIKE \"DELETE FROM %\"";
        $res = $this->_databaseLogic->getLastRowsWithWhereStatement(array("text"), "tl_log", $whereStatement)
            ->fetchAllAssoc();

        $deleteStatements = array();
        foreach ($res as $statement) {
            $deleteStatements[] = $statement["text"];
        }
        $this->_prodDatabaseLogic->runSqlCommandsOnProdDatabase($deleteStatements);
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
