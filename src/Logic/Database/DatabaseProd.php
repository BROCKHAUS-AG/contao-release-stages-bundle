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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseExecutionFailure;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use PDO;
use PDOException;

class DatabaseProd
{
    private Config $_config;
    private PDO $_conn;
    public string $_databaseName;

    public function __construct(Config $config)
    {
        $this->_config = $config;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function setUp(): void
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

    private function getDatabaseConfiguration(): Database
    {
        return $this->_config->getDatabaseConfiguration();
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
