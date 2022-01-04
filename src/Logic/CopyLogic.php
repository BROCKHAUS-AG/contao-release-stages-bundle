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

// tl_news_categories has no id column !!!!!!! must be fixed in copyToDatabase !!!!!
DEFINE("DO_NOT_COPY_TABLES", array("tl_release_stages", "tl_contao_bundle_creator", "tl_news_categories", "tl_log", "dokumentanforderung", "webinaranmeldung", "webinaraufnahme"));

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
            $this->runInsertCommands($conn, $commandsToBeExecuted);
        }
        $this->runInsertCommands($conn, $this->createLogForProd());
        $conn->close();
    }

    public function downloadFromDatabase() : array
    {
        $tableNames = $this->getTableNamesFromDatabase();
        $table = array();
        foreach ($tableNames as $tableName)
        {
            $tableContent = $this->Database->prepare("SELECT * FROM contao.". $tableName)
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

        if ($req->num_rows > 0) {
            $row = $req->fetch_assoc();
            $lastId = $row["id"];
        }else {
            $lastId = "0";
        }
        $values = $this->createValuesForCommand($tableContent, $lastId);
        return $this->createCommands($values, $tableName);
    }

    private function createValuesForCommand(array $tableContent, string $lastId) : array
    {
        $values = array();
        foreach ($tableContent as $column) {
            if (intval($column["id"]) > intval($lastId)) {
                $values[] = $this->createValueForCommand($column);
            }
        }
        return $values;
    }

    private function createValueForCommand(array $column) : string
    {
        $value = "";
        $index = 1;
        foreach ($column as $x) {
            if ($x == null || strcmp($x, "") == 0) {
                $value .= "''";
            }else {
                $value .= "'". $x. "'";
            }
            if ($index < count($column)) {
                $value .= ", ";
            }
            $index++;
        }
        return $value;
    }

    private function createCommands(array $values, string $tableName) : array
    {
        $commandsToBeExecuted = array();
        foreach ($values as $value) {
            $commandsToBeExecuted[] = "INSERT INTO ". PROD_DATABASE. ".". $tableName. " VALUES (". $value. ");";
        }
        if ($commandsToBeExecuted == null) return array();
        return $commandsToBeExecuted;
    }

    private function createLogForProd() : array
    {
        return array("INSERT INTO ". PROD_DATABASE. ".tl_log (tstamp, action, username, text, func, browser) VALUES
        ('". time(). "', 'RELEASE', 'release', 'Released a new version', 'auto release', 'N/A');");
    }

    private function runInsertCommands(mysqli $conn, array $commandsToBeExecuted) : void
    {
        if ($commandsToBeExecuted != null) {
            foreach ($commandsToBeExecuted as $command) {
                if ($conn->query($command) === FALSE) {
                    echo "Datenbank konnte nicht aktualisiert werden! Es ist ein Fehler aufgetreten :)</br>";
                    echo $conn->error;
                    die;
                }
            }
        }
    }
}
