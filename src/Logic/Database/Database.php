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

use BrockhausAg\ContaoReleaseStagesBundle\Constants\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\DeploymentState;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Validation;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Database\TableInformation;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Database\TableInformationCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Version\Version;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

class Database
{
    private Connection $_dbConnection;
    private Config $_config;

    public function __construct(Connection $dbConnection, Config $config)
    {
        $this->_dbConnection = $dbConnection;
        $this->_config = $config;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function isTableEmpty(string $table): bool
    {
        $result = $this->_dbConnection
            ->createQueryBuilder()
            ->select("*")
            ->from($table)
            ->execute()
            ->fetchAllAssociative();
        return $result == NULL;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function hasTableOneRow(string $table): bool
    {
        $result = $this->_dbConnection
            ->createQueryBuilder()
            ->select("*")
            ->from($table)
            ->execute()
            ->fetchAllAssociative();
        return count($result) == 1;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getKindOfReleaseById(int $id): string
    {
        $result = $this->_dbConnection
            ->createQueryBuilder()
            ->select("kindOfRelease")
            ->from(Constants::DEPLOYMENT_TABLE)
            ->where("id = :id")
            ->setParameter("id", $id)
            ->execute()
            ->fetchAssociative();
        return $result["kindOfRelease"];
    }

    /**
     * @throws DatabaseQueryEmptyResult
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     * @throws Validation
     */
    public function getLatestReleaseVersion(): Version
    {
        $result = $this->_dbConnection
            ->createQueryBuilder()
            ->select("id", "version", "kindOfRelease", "state")
            ->from(Constants::DEPLOYMENT_TABLE)
            ->orderBy("id", "DESC")
            ->setMaxResults(2)
            ->execute()
            ->fetchAllAssociative();

        if ($result[1] == NULL || $result[1]["version"] == NULL) {
            throw new DatabaseQueryEmptyResult();
        }

        $latestVersion = $result[1];
        return new Version(intval($latestVersion["id"]), $latestVersion["kindOfRelease"], $latestVersion["version"],
            DeploymentState::PENDING);
    }


    /**
     * @throws Exception
     */
    public function updateVersion(int $id, string $version) : void
    {
        $this->_dbConnection
            ->createQueryBuilder()
            ->update(Constants::DEPLOYMENT_TABLE)
            ->set("version", ":version")
            ->where("id = :id")
            ->setParameter("version", $version)
            ->setParameter("id", $id)
            ->execute();
    }

    /**
     * @throws Exception
     */
    public function updateState(string $state, int $id, int $executionTime, string $information, bool $rollback): void
    {
        $this->_dbConnection
            ->createQueryBuilder()
            ->update(Constants::DEPLOYMENT_TABLE)
            ->set("state", ":state")
            ->set("information", ":information")
            ->set("execution_time", ":execution_time")
            ->set("rollback", ":rollback")
            ->where("id = :id")
            ->setParameter("state", $state)
            ->setParameter("information", $information)
            ->setParameter("execution_time", $executionTime)
            ->setParameter("rollback", $rollback ? 1 : 0)
            ->setParameter("id", $id)
            ->execute();
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getFullTableInformation(): TableInformationCollection
    {
        $tableNames = $this->getTableNamesFromDatabase();
        return $this->getTableInformationByTableNames($tableNames);
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function getTableNamesFromDatabase() : array
    {
        $testStageDatabaseName = $this->_dbConnection->getDatabase();

        $tables = $this->_dbConnection
            ->createQueryBuilder()
            ->select("TABLE_NAME")
            ->from("INFORMATION_SCHEMA.TABLES")
            ->where("TABLE_SCHEMA = :tableSchema")
            ->andWhere("TABLE_NAME LIKE \"tl_%\"")
            ->setParameter("tableSchema", $testStageDatabaseName)
            ->execute()
            ->fetchAllAssociative();

        $ignoredTables = $this->getIgnoredTablesFromConfigurationFile();
        return $this->filterTables($tables, $ignoredTables);
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function getTableInformationByTableNames(array $tableNames): TableInformationCollection
    {
        $tableInformation = new TableInformationCollection();
        foreach ($tableNames as $tableName)
        {
            $tableContent = $this->_dbConnection
                ->createQueryBuilder()
                ->select("*")
                ->from($tableName)
                ->execute()
                ->fetchAllAssociative();
            $tableInformation->add(new TableInformation($tableName, $tableContent));
        }
        return $tableInformation;
    }

    private function getIgnoredTablesFromConfigurationFile() : array
    {
        return $this->_config->getDatabaseIgnoredTablesConfiguration();
    }

    private function filterTables(array $tables, array $ignoredTables): array
    {
        $tableNames = array();
        foreach ($tables as $table) {
            $tableName = $table["TABLE_NAME"];
            if (!in_array($tableName, $ignoredTables)) {
                $tableNames[] = $tableName;
            }
        }
        return $tableNames;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function getTableScheme(string $table): array
    {
        return $this->_dbConnection
            ->prepare("DESCRIBE ". $table)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function loadHexById(string $column, string $tableName, string $id): array
    {
        return $this->_dbConnection
            ->createQueryBuilder()
            ->select("$column")
            ->from($tableName)
            ->where("id = :id")
            ->setParameter("id", $id)
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * @throws DatabaseQueryEmptyResult
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getActualIdFromTable(string $table): int
    {
        $result = $this->_dbConnection
            ->createQueryBuilder()
            ->select("id")
            ->from($table)
            ->orderBy("id", "DESC")
            ->setMaxResults(1)
            ->execute()
            ->fetchAllAssociative();

        if ($result[0] == NULL || $result[0]["id"] == NULL) {
            throw new DatabaseQueryEmptyResult();
        }
        return intval($result[0]["id"]);
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function isOldDeploymentPending(int $actualId): bool
    {
        $pendingResult = $this->_dbConnection
            ->createQueryBuilder()
            ->select("state")
            ->from(Constants::DEPLOYMENT_TABLE)
            ->where("state = :state")
            ->andWhere("id != :id")
            ->setParameter("state", DeploymentState::PENDING)
            ->setParameter("id", $actualId)
            ->execute()
            ->fetchAllAssociative();

        if ($pendingResult == null) {
            return false;
        }
        $state = $pendingResult[0]["state"];
        return $state == DeploymentState::PENDING;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DatabaseQueryEmptyResult
     */
    public function getDeleteStatementsFromTlLogTable(): array
    {
        $result = $this->_dbConnection
            ->createQueryBuilder()
            ->select("text")
            ->from("tl_log")
            ->where("text LIKE 'DELETE FROM %'")
            ->execute()
            ->fetchAllAssociative();

        return $result;
    }
}
