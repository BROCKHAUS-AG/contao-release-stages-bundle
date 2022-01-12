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
use mysqli;

class ProdDatabaseLogic
{
    private IOLogic $_ioLogic;
    private mysqli $_conn;
    public string $prodDatabase;

    public function __construct()
    {
        $this->_ioLogic = new IOLogic();
        $config = $this->getDatabaseConfiguration();
        $this->prodDatabase = $config["name"];
        $this->_conn = $this->createConnectionToProdDatabase($config["server"], $config["username"],
            $config["password"], $config["name"], $config["port"]);
    }

    private function getDatabaseConfiguration() : array
    {
        $config = $this->_ioLogic->loadDatabaseConfiguration();

        return array(
            "server" => $config["server"],
            "name" => $config["name"],
            "port" => $config["port"],
            "username" => $config["username"],
            "password" => $config["password"]
        );
    }

    private function createConnectionToProdDatabase(string $server, string $user, string $password, string $database,
                                                    int $port)
    {
        $conn = new mysqli($server, $user, $password, $database, $port);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    public function getTableSchemes(string $tableName) : array
    {
        $sql = "DESCRIBE ". $this->prodDatabase. ".". $tableName;
        $req = $this->_conn->query($sql);
        $tableSchemes = array();
        if ($req->num_rows > 0) {
            while($tableScheme = $req->fetch_assoc()) {
                $tableSchemes[] = array(
                    "field" => $tableScheme["Field"],
                    "type" => $tableScheme["Type"],
                    "nullable" => $tableScheme["Null"]
                );
            }
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
        $sql = "SELECT id FROM ". $this->prodDatabase. ".". $tableName. " ORDER BY id DESC LIMIT 1";
        $req = $this->_conn->query($sql);

        if ($req->num_rows <= 0) {
            echo "<br/>Es ist ein Fehler aufgetreten :)</br>Fehler: ". $this->_conn->error;
            die;
        }
        $row = $req->fetch_assoc();
        return intval($row["id"]);
    }
}