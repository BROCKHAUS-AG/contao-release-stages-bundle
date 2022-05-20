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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\State\NoSubmittedPendingState;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\State\OldStateIsPending;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Validation;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Database\TableInformationCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Database\TableInformation;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Version\Version;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemVariables;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Exception;

class Database
{
    private Connection $_dbConnection;
    private IO $_io;

    public function __construct(Connection $dbConnection, IO $io)
    {
        $this->_dbConnection = $dbConnection;
        $this->_io = $io;
    }

    /**
     * @throws DatabaseQueryEmptyResult
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws Validation
     */
    public function getLatestReleaseVersion(): Version
    {
        $result = $this->_dbConnection
            ->createQueryBuilder()
            ->select("id", "version", "kindOfRelease", "state")
            ->from("tl_release_stages")
            ->orderBy("id", "DESC")
            ->setMaxResults(2)
            ->execute()
            ->fetchAllAssociative();

        if ($result[1] == NULL || $result[1]["version"] == NULL) {
            throw new DatabaseQueryEmptyResult();
        }

        $latestVersion = $result[1];
        return new Version(intval($latestVersion["id"]), $latestVersion["kindOfRelease"], $latestVersion["version"],
            SystemVariables::STATE_PENDING);
    }


    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateVersion(int $id, string $version) : void
    {
        $this->_dbConnection
            ->createQueryBuilder()
            ->update("tl_release_stages")
            ->set("version", ":version")
            ->where("id = :id")
            ->setParameter("version", $version)
            ->setParameter("id", $id)
            ->execute();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateState(string $state, int $id){
        $this->_dbConnection
            ->createQueryBuilder()
            ->update("tl_release_stages")
            ->set("state", ":state")
            ->where("id = :id")
            ->setParameter("state", $state)
            ->setParameter("id", $id)
            ->execute();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getFullTableInformation(): TableInformationCollection
    {
        $tableNames = $this->getTableNamesFromDatabase();
        return $this->getTableInformationByTableNames($tableNames);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
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
     * @throws \Doctrine\DBAL\Exception
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
        return $this->_io->getDatabaseIgnoredTablesConfiguration();
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
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTableScheme(string $table): array
    {
        return $this->_dbConnection
            ->prepare("DESCRIBE ". $table)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function loadHexById(string $column, string $tableName, string $id): array
    {
        return $this->_dbConnection
            ->createQueryBuilder()
            ->select("hex(:column)")
            ->from($tableName)
            ->where("id = :id")
            ->setParameter("column", $column)
            ->setParameter("id", $id)
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function checkForDeletedFilesInTlLogTable() : Result
    {
        return $this->_dbConnection
            ->executeQuery("SELECT text FROM tl_log WHERE text LIKE 'File or folder % has been deleted'");
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getRowsFromTlLogTableWhereIdIsBiggerThanIdAndTextIsLikeDeleteFrom(int $lastId): array
    {
        return $this->_dbConnection
            ->createQueryBuilder()
            ->select("text")
            ->from("tl_log")
            ->where("id > :id")
            ->andWhere("text LIKE 'DELETE FROM %'")
            ->setParameter("id", $lastId)
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * @throws DatabaseQueryEmptyResult
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getActualIdFromTlReleaseStages(): int
    {
        $result = $this->_dbConnection
            ->createQueryBuilder()
            ->select("id")
            ->from("tl_release_stages")
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
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws OldStateIsPending
     * @throws NoSubmittedPendingState
     * @throws DatabaseQueryEmptyResult
     */
    public function checkLatestState(): void
    {
        $actualId = $this->getActualIdFromTlReleaseStages();
        $result = $this->_dbConnection
            ->createQueryBuilder()
            ->select("state")
            ->from("tl_release_stages")
            ->where("state = :state")
            ->andWhere("id != :id")
            ->setParameter("state", SystemVariables::STATE_PENDING)
            ->setParameter("id", $actualId)
            ->execute()
            ->fetchAllAssociative();
        if ($result == null) {
            throw new NoSubmittedPendingState();
        }
        $state = $result[0]["state"];
        if ($state == SystemVariables::STATE_PENDING) {
            throw new OldStateIsPending();
        }
    }
}
