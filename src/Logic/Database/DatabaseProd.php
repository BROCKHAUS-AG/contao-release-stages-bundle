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
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use PDO;
use PDOException;

class DatabaseProd
{
    private IO $_ioLogic;
    private Log $_log;
    private PDO $_conn;
    public string $_databaseName;

    public function __construct(IO $ioLogic, Log $log)
    {
        $this->_ioLogic = $ioLogic;
        $this->_log = $log;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function setUpDatabaseConnection(): void
    {
        $config = $this->getDatabaseConfiguration();
        $this->_databaseName = $config->getName();
        $this->_conn = $this->createConnectionToProdDatabase($config);
    }


    /**
     * @throws DatabaseQueryEmptyResult
     */
    public function getTableSchemes(string $tableName): array
    {
        $statement = $this->_conn->prepare("DESCRIBE ". $tableName);
        $statement->execute();

        $tableSchemes = array();
        if ($statement->rowCount() <= 0) {
            throw new DatabaseQueryEmptyResult("Database executed query returned null");
        }

        while($tableScheme = $statement->fetch()) {
            $tableSchemes[] = $this->createReturnValueForTableSchemes($tableScheme);
        }

        return $tableSchemes;
    }

    private function createReturnValueForTableSchemes(array $tableScheme): array
    {
        return array(
            "field" => $tableScheme["Field"],
            "type" => $tableScheme["Type"],
            "nullable" => $tableScheme["Null"]
        );
    }

    public function checkIfTableExists(string $table): bool
    {
        $statement = $this->_conn->prepare("DESCRIBE ". $table);
        $statement->execute();
        if ($statement->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @throws DatabaseExecutionFailure
     */
    public function createTable(string $table, array $tableScheme): void
    {
        $tableCommand = $this->createCreateTableCommand($table, $tableScheme);
        $this->runCreateTableCommand($tableCommand);
    }

    private function createCreateTableCommand(string $table, array $tableScheme): string
    {
        $tableCommand = "CREATE TABLE ". $table. "(";
        $primaryKey = "";
        for ($x = 0; $x != count($tableScheme); $x++)
        {
            $scheme = $tableScheme[$x];
            $defaultValue = $this->setDefaultValueForAttribute($scheme["Null"]);
            $tableCommand = $tableCommand. $scheme["Field"]. " ". $scheme["Type"]. " ".
                $defaultValue. ", ";
            if (isset($scheme["Key"]) && $scheme["Key"] == "PRI") {
                $primaryKey = $scheme["Field"];
            }
        }
        return $tableCommand. "PRIMARY KEY(". $primaryKey. "));";
    }

    private function setDefaultValueForAttribute(string $scheme): string
    {
        return $scheme == "YES" ? "NULL" : "NOT NULL";
    }

    /**
     * @throws DatabaseExecutionFailure
     */
    private function runCreateTableCommand(string $createTableCommand): void
    {
        $statement = $this->_conn->prepare($createTableCommand);
        try {
            $statement->execute();
        }catch (PDOException $e) {
            throw new DatabaseExecutionFailure($e->getMessage());
        }
    }

    /**
     * @throws DatabaseExecutionFailure
     */
    public function executeCommands(array $commands): void
    {
        foreach ($commands as $command) {
            if ($this->_conn->query($command) === FALSE) {
                throw new DatabaseExecutionFailure($this->_conn->errorCode());
            }
        }
    }

    /**
     * @throws DatabaseQueryEmptyResult
     */
    public function getLastIdFromTlLogTable(): int
    {
        $table = $this->_databaseName. ".tl_log";
        $statement = $this->_conn
            ->prepare("SELECT id FROM ? ORDER BY id DESC LIMIT 1");

        try {
            $statement->execute(array($table));
            $result = $statement->fetch();
        }catch (PDOException $e) {
            throw new DatabaseQueryEmptyResult($e->getMessage());
        }

        return intval($result["id"]);
    }

    private function getDatabaseConfiguration(): Database
    {
        return $this->_ioLogic->getDatabaseConfiguration();
    }

    private function createConnectionToProdDatabase(Database $database): PDO
    {
        $connectionString = $this->createConnectionString($database);
        return new PDO($connectionString, $database->getUsername(), $database->getPassword());
    }

    private function createConnectionString(Database $database): string
    {
        return sprintf( "mysql:host=%s;dbname=%s;port=%d", $database->getServer(), $database->getName(),
            $database->getPort());
    }
}
