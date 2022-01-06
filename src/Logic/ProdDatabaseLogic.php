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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic;

use mysqli;

DEFINE("PROD_SERVER", "192.168.0.2");
DEFINE("PROD_DATABASE", "prodContao");
DEFINE("PROD_USER", "prodContao");
DEFINE("PROD_USER_PASSWORD", "admin1234");

class ProdDatabaseLogic
{
    private mysqli $_conn;

    public function __construct()
    {
        // create conn
        $this->_conn = $this->createConnectionToProdDatabase(PROD_SERVER, PROD_USER,
            PROD_USER_PASSWORD, PROD_DATABASE);
    }

    private function createConnectionToProdDatabase(string $server, string $user, string $password, string $database)
    {
        $conn = new mysqli($server, $user, $password, $database);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    public function getTableSchemes(string $tableName) : array
    {
        $sql = "DESCRIBE ". PROD_DATABASE. ".". $tableName;
        $req = $this->_conn->query($sql);
        $tableSchemes = array();
        while($tableScheme = $req->fetch_assoc()) {
            $tableSchemes[] = array(
                "field" => $tableScheme["Field"],
                "type" => $tableScheme["Type"],
                "nullable" => $tableScheme["Null"]
            );
        }
        return $tableSchemes;
    }

    public function runSqlCommandsOnProdDatabase(array $commandsToBeExecuted) : void
    {
        if ($commandsToBeExecuted != null) {
            foreach ($commandsToBeExecuted as $command) {
                if ($this->_conn->query($command) === FALSE) {
                    echo "<br/>Es ist ein Fehler aufgetreten :)</br>Fehler: ". $this->_conn->error;
                    die;
                }
            }
        }
    }

    public function getLastIdFromTable(string $tableName) : int
    {
        $sql = "SELECT id FROM ". PROD_DATABASE.$tableName. " ORDER BY id DESC LIMIT 1";
        $req = $this->_conn->query($sql);
        echo $tableName. "</br>";

        $lastId = 0;
        if ($req->num_rows > 0) {
            $row = $req->fetch_assoc();
            $lastId = intval($row["id"]);
        }
        return $lastId;
    }
}
