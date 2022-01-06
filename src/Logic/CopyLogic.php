<?php

declare(strict_types=1);

/*
 * This file is part of contao-release-stages-bundle.
 *
 * (c) BROCKHAUS AG 2021 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-release-stages-bundle
 */

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic;

use Contao\Backend;
use mysqli;

DEFINE("PROD_SERVER", "192.168.0.2");
DEFINE("PROD_DATABASE", "prodContao");
DEFINE("PROD_USER", "prodContao");
DEFINE("PROD_USER_PASSWORD", "admin1234");

// should never be copied: "tl_release_stages", "tl_contao_bundle_creator", "tl_log", "tl_cron_job", "tl_user"

// tl_search_index: in einem wort ist ein: '    SELECT * FROM `tl_search_index` WHERE word like 'girls%';
// same at tl_search: SELECT * FROM `tl_search` WHERE text like '%den Lokalnachrichten Bergkamen%';
// same at tl_page


DEFINE("DO_NOT_COPY_TABLES", array("tl_release_stages", "tl_contao_bundle_creator", "tl_cron_job", "tl_user",
    "tl_content", "tl_news", "tl_news_archive", "tl_news_category", "tl_page", "tl_search", "tl_search_index",
    "tl_url_rewrite", "tl_undo", "tl_version"));

class CopyLogic extends Backend
{

    public function copyToDatabase() : void
    {
        $tables = $this->downloadFromDatabase();

        $conn = $this->createConnectionToProdDatabase();

        echo "to be inserted into table: </br>";
        foreach ($tables as $table) {
            $tableName = $table[0];
            $tableContent = $table[1];
            $commandsToBeExecuted = $this->create($conn, $tableName, $tableContent);
            $this->runSqlCommandsOnProdDatabase($conn, $commandsToBeExecuted);
        }
        $conn->close();
    }

    public function downloadFromDatabase() : array
    {
        $tableNames = $this->getTableNamesFromDatabase();
        $table = array();
        foreach ($tableNames as $tableName)
        {
            $tableContent = $this->Database->prepare("SELECT * FROM ". $tableName)
                ->execute()
                ->fetchAllAssoc();
            $table[] = array($tableName, $tableContent);
        }
        return $table;
    }

    private function getTableNamesFromDatabase() : array
    {
        $tables = $this->Database->prepare("SHOW TABLES FROM contao")
            ->execute();
        $tableNames = array();
        while ($tables->next()) {
            $tableName = $tables->Tables_in_contao;
            if (!in_array($tableName, DO_NOT_COPY_TABLES)) {
                $tableNames[] = $tableName;
            }
        }
        return $tableNames;
    }

    private function createConnectionToProdDatabase()
    {
        $conn = new mysqli(PROD_SERVER, PROD_USER, PROD_USER_PASSWORD, PROD_DATABASE);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    private function create(mysqli $conn, string $tableName, array $tableContent) : array
    {
        $sql = "SELECT id FROM ". PROD_DATABASE.$tableName. " ORDER BY id DESC LIMIT 1";
        $req = $conn->query($sql);
        echo $tableName. "</br>";

        $lastId = 0;
        if ($req->num_rows > 0) {
            $row = $req->fetch_assoc();
            $lastId = intval($row["id"]);
        }

        if (strcmp($tableName, "tl_log") == 0) {
            $this->checkForDeleteFromInTlLogTable($lastId, $conn);
        }

        $values = $this->createColumnWithValuesForCommand($conn, $tableName, $tableContent);
        return $this->createCommands($values, $tableName);
    }

    private function checkForDeleteFromInTlLogTable(int $lastId, mysqli $conn) : void
    {
        $res = $this->Database->prepare("SELECT text from tl_log WHERE id > ". $lastId.
            " AND text LIKE \"DELETE FROM %\"")
            ->execute()
            ->fetchAllAssoc();
        $deleteStatements = array();
        foreach ($res as $statement) {
            $deleteStatements[] = $statement["text"];
        }
        $this->runSqlCommandsOnProdDatabase($conn, $deleteStatements);
    }

    private function createColumnWithValuesForCommand(mysqli $conn, string $tableName, array $tableContent) : array
    {
        $sql = "DESCRIBE ". PROD_DATABASE. ".". $tableName;
        $req = $conn->query($sql);
        $tableSchemes = array();
        while($tableScheme = $req->fetch_assoc()) {
            $tableSchemes[] = array(
                "field" => $tableScheme["Field"],
                "type" => $tableScheme["Type"],
                "nullable" => $tableScheme["Null"]
            );
        }

        $values = array();
        foreach ($tableContent as $column) {
            $values[] = $this->createColumnWithValueForCommand($column, $tableSchemes, $tableName);
        }
        return $values;
    }

    private function createColumnWithValueForCommand(array $column, array $tableSchemes, string $tableName) : array
    {
        $index = 0;
        $tableSchemesFields = array();
        $rows = array();
        $columnAndValue = array();
        foreach ($column as $row) {
            if (strpos($tableSchemes[$index]["type"], "varchar") ||
                strpos($tableSchemes[$index]["type"], "string") ||
                strpos($tableSchemes[$index]["type"], "char") ||
                strcmp($tableSchemes[$index]["type"], "char(1)") == 0 ||
                strpos($tableSchemes[$index]["type"], "text")) {
                $rows[] = '\''. $row. '\'';
                $columnAndValue[] = $tableSchemes[$index]["field"]. " = '". $row. "'";
            }else if (empty($row) && strcmp($tableSchemes[$index]["nullable"], "YES") == 0) {
                $rows[] = "NULL";
                $columnAndValue[] = $tableSchemes[$index]["field"]. " = NULL";
            }else if (strcmp($tableSchemes[$index]["type"], "binary(16)") == 0) {
                // load hex from db and add for upload to prod db
                $req = $this->Database->prepare("SELECT hex(". $tableSchemes[$index]["field"]. ") FROM ".
                    $tableName. " WHERE id = ". $column["id"])
                    ->execute(1)
                    ->fetchAllAssoc();
                $rows[] = "UNHEX('". $req[0]["hex(singleSRC)"]. "')";
                $columnAndValue[] = $tableSchemes[$index]["field"]. "= UNHEX('". $req[0]["hex(singleSRC)"]. "')";
            }else if (strcmp($tableSchemes[$index]["type"], "blob") == 0 ||
                strcmp($tableSchemes[$index]["type"], "mediumblob") == 0 ||
                strpos($tableSchemes[$index]["type"], "varbinary")) {
                $rows[] = "NULL";
                $columnAndValue[] = $tableSchemes[$index]["field"]. " = NULL";
            }else {
                $rows[] = $row;
                $columnAndValue[] = $tableSchemes[$index]["field"]. " = ". $row;
            }

            $tableSchemesFields[] = $tableSchemes[$index]["field"];
            $index++;
        }
        $value = implode(", ", $rows);
        $columnName = implode(", ", $tableSchemesFields);

        $updateColumnAndValue = implode(", ", $columnAndValue);

        return array($columnName, $value, $updateColumnAndValue);
    }

    private function createCommands(array $values, string $tableName) : array
    {
        $commandsToBeExecuted = array();
        foreach ($values as $value) {
            $commandsToBeExecuted[]
                = 'INSERT INTO '. PROD_DATABASE. '.'. $tableName. ' ('. $value[0]. ') VALUES ('. $value[1].
                ') ON DUPLICATE KEY UPDATE '. $value[2]. ';';
        }
        if ($commandsToBeExecuted == null) return array();
        return $commandsToBeExecuted;
    }

    private function runSqlCommandsOnProdDatabase(mysqli $conn, array $commandsToBeExecuted) : void
    {
        if ($commandsToBeExecuted != null) {
            foreach ($commandsToBeExecuted as $command) {
                if ($conn->query($command) === FALSE) {
                    echo "<br/>Es ist ein Fehler aufgetreten :)</br>Fehler: ". $conn->error;
                    die;
                }
            }
        }
    }
}
