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

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IOLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use Doctrine\DBAL\Connection;
use mysqli;

class ProdDatabaseLogic
{
    private Connection $_dbConnection;
    private IOLogic $_ioLogic;
    private Log $_log;
    private mysqli $_conn;
    public string $prodDatabase;

    public function __construct(Connection $dbConnection, IOLogic $ioLogic, Log $log)
    {
        $this->_dbConnection = $dbConnection;
        $this->_ioLogic = $ioLogic;
        $this->_log = $log;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function setUpDatabaseConnection(): void
    {
        $config = $this->getDatabaseConfiguration();
        $this->prodDatabase = $config->getName();
        $this->_conn = $this->createConnectionToProdDatabase($config);
    }

    public function getTableSchemes(string $tableName): array
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

    public function runSqlCommandsOnProdDatabase(array $commandsToBeExecuted): void
    {
        if ($commandsToBeExecuted != null) {
            foreach ($commandsToBeExecuted as $command) {
                if ($this->_conn->query($command) === FALSE) {
                    $this->_log->warning("Something went wrong at running sql commands on prod database: ".
                        $this->_conn->error);
                }
            }
        }
    }

    public function getLastIdFromTable(string $tableName): int
    {
        $sql = "SELECT id FROM ". $this->prodDatabase. ".". $tableName. " ORDER BY id DESC LIMIT 1";
        $req = $this->_conn->query($sql);

        if ($req->num_rows <= 0) {
            $this->_log->logErrorAndDie("Something went wrong at running sql commands on prod database: ".
                $this->_conn->error);
        }
        $row = $req->fetch_assoc();
        return intval($row["id"]);
    }

    private function getDatabaseConfiguration(): Database
    {
        return $this->_ioLogic->getDatabaseConfiguration();
    }

    private function createConnectionToProdDatabase(Database $database): mysqli
    {
        $conn = new mysqli($database->getServer(), $database->getUsername(), $database->getPassword(),
            $this->_dbConnection->getDatabase(), $database->getPort());
        if ($conn->connect_error) {
            $error_message = "Connection failed: " . $conn->connect_error;
            $this->_log->logErrorAndDie($error_message);
        }
        return $conn;
    }
}
