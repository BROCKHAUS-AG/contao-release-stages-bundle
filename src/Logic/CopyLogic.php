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
DEFINE("DO_NOT_COPY_TABLES", array("tl_release_stages", "tl_contao_bundle_creator", "tl_news_categories"));

class CopyLogic extends Backend
{

    public function copyToDatabase() : void
    {
        $tables = $this->downloadFromDatabase();

        $conn = $this->createConnectionToProdDatabase();

        $commandsToBeExecuted = null;

        echo "to be inserted into table: </br>";
        foreach ($tables as $table) {
            $tableName = $table[0];
            $tableContent = $table[1];

            $sql = "SELECT id FROM ". $tableName. " ORDER BY id DESC LIMIT 1";

            $req = $conn->query($sql);
            if ($req->num_rows > 0) {
                echo $tableName. "</br>";

                $row = $req->fetch_assoc();
                $lastId = $row["id"];


                $values = array();
                foreach ($tableContent as $column) {
                    $value = "";
                    $index = 1;
                    if (intval($column["id"]) > intval($lastId)) {
                        foreach ($column as $y) {
                            if ($y == null || strcmp($y, "") == 0) {
                                $value .= "''";
                            }else {
                                $value .= "'". $y. "'";
                            }
                            if ($index < count($column)) {
                                $value .= ", ";
                            }
                            $index++;
                        }
                        $values[] = $value;
                    }
                }

                foreach ($values as $value) {
                    $commandsToBeExecuted .= "INSERT INTO ". PROD_DATABASE. ".". $tableName. " VALUES (". $value. ");";
                }
            }
        }

        if ($commandsToBeExecuted != null) {
            if ($conn->multi_query($commandsToBeExecuted) === FALSE) {
                echo "Datenbank konnte nicht aktualisiert werden! Es ist ein Fehler aufgetreten :)";
            }
        }else {
            echo "Die Datenbank wurde nicht aktualisiert, da keine Änderungen vorliegen!";
            die;
        }

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
}
