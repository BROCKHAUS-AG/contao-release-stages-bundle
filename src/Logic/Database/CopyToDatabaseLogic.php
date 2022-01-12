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

use BrockhausAg\ContaoReleaseStagesBundle\Logic\IOLogic;
use Contao\Backend;

class CopyToDatabaseLogic extends Backend
{
    private DatabaseLogic $_databaseLogic;
    private ProdDatabaseLogic $_prodDatabaseLogic;
    private IOLogic $_ioLogic;

    public function __construct()
    {
        $this->_databaseLogic = new DatabaseLogic();
        $this->_prodDatabaseLogic = new ProdDatabaseLogic();
        $this->_ioLogic = new IOLogic();
    }

    public function copyToDatabase() : void
    {
        $tables = $this->_databaseLogic->downloadFromDatabase();

        echo "to be inserted into/updated table: </br>";
        foreach ($tables as $table) {
            $tableName = $table[0];
            $tableContent = $table[1];
            echo $tableName. "</br>";
            $commandsToBeExecuted = $this->create($tableName, $tableContent);
            $this->_prodDatabaseLogic->runSqlCommandsOnProdDatabase($commandsToBeExecuted);
        }

        $lastId = $this->_prodDatabaseLogic->getLastIdFromTable("tl_log");
        $this->checkForDeleteFromInTlLogTable($lastId);
    }

    private function create(string $tableName, array $tableContent) : array
    {
        $values = $this->createColumnWithValuesForCommand($tableName, $tableContent);
        return $this->createCommands($values, $tableName);
    }

    private function checkForDeleteFromInTlLogTable(int $lastId) : void
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

    private function createColumnWithValuesForCommand(string $tableName, array $tableContent) : array
    {
        $tableSchemes = $this->_prodDatabaseLogic->getTableSchemes($tableName);
        $values = array();
        foreach ($tableContent as $column) {
            if (strcmp($tableName, "tl_page") == 0 && strcmp($column["type"], "root") == 0) {
                $column["dns"] = $this->changeDNSEntryForProd($column["alias"]);
            }
            $values[] = $this->createColumnWithValueForCommand($column, $tableSchemes, $tableName);
        }
        return $values;
    }

    private function createColumnWithValueForCommand(array $column, array $tableSchemes, string $tableName) : array
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

    private function createReturnValue(array $rows, array $tableSchemeFields, array $columnAndValue) : array
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

    private function changeDNSEntryForProd(string $alias) : string
    {
        $dnsRecords = $this->_ioLogic->loadDNSRecords();
        foreach ($dnsRecords as $dnsRecord) {
            if (strcmp($dnsRecord["alias"], $alias) == 0) {
                return $dnsRecord["dns"];
            }
        }
        return "";
    }

    private function createCommands(array $values, string $tableName) : array
    {
        $commandsToBeExecuted = array();
        foreach ($values as $value) {
            $commandsToBeExecuted[]
                = 'INSERT INTO '. $this->_prodDatabaseLogic->prodDatabase. '.'. $tableName. ' ('. $value["columnName"].
                ') VALUES ('. $value["value"]. ') ON DUPLICATE KEY UPDATE '. $value["updateColumnAndValue"]. ';';
        }
        if ($commandsToBeExecuted == null) return array();
        return $commandsToBeExecuted;
    }
}