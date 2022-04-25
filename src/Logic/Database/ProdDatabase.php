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
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use Doctrine\DBAL\Connection;
use PDO;

class ProdDatabase
{
    private Connection $_prodDatabaseConnection;
    private IO $_ioLogic;
    private Log $_log;
    private PDO $_conn;
    public string $_prodDatabaseName;

    public function __construct(Connection $prodDatabaseConnection, IO $ioLogic, Log $log)
    {
        $this->_prodDatabaseConnection = $prodDatabaseConnection;
        $this->_ioLogic = $ioLogic;
        $this->_log = $log;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function setUpDatabaseConnection(): void
    {
        $config = $this->getDatabaseConfiguration();
        $this->_prodDatabaseName = $config->getName();
        $this->_conn = $this->createConnectionToProdDatabase($config);
    }

    public function getTableSchemes(string $tableName): array
    {
        $table = $this->_prodDatabaseName. ".". $tableName;
        $statement = $this->_conn->prepare("DESCRIBE ?");
        $statement->execute(array($table));

        $tableSchemes = array();
        if ($statement->rowCount() > 0) {
            while($tableScheme = $statement->fetch()) {
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
                    $this->_log->warning("Something went wrong at running sql commands on prod database: Error-Code: ".
                        $this->_conn->errorCode());
                }
            }
        }
    }

    public function getLastIdFromTlLogTable(): int
    {
        $table = $this->_prodDatabaseName. ".tl_log";
        $statement = $this->_conn
            ->prepare("SELECT id FROM ? ORDER BY id DESC LIMIT 1");
        $statement->execute(array($table));
        $result = $statement->fetch();

        if ($statement->rowCount() <= 0) {
            $this->_log->logErrorAndDie("Something went wrong at running sql commands on prod database: Error-Code: ".
                $this->_conn->errorCode());
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
