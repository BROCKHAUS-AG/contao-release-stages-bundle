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
use BrockhausAg\ContaoReleaseStagesBundle\Model\Version\Version;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Exception;

class DatabaseLogic
{
    private Connection $_dbConnection;
    private IOLogic $_ioLogic;

    public function __construct(Connection $dbConnection, IOLogic $ioLogic)
    {
        $this->_dbConnection = $dbConnection;
        $this->_ioLogic = $ioLogic;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getLatestReleaseVersion(): Version
    {
        $result = $this->_dbConnection->createQueryBuilder()
            ->select("id", "version", "kindOfRelease")
            ->from("tl_release_stages")
            ->orderBy("id", "DESC")
            ->setMaxResults(2)
            ->execute()
            ->fetchAllAssociative();

        if ($result[1] == NULL || $result[1]["version"] == NULL) {
            throw new Exception("no entry found");
        }

        $latestVersion = $result[1];
        return new Version(intval($latestVersion["id"]), $latestVersion["kindOfRelease"], $latestVersion["version"]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getLastRowsWithWhereStatement(array $columns, string $tableName, string $whereStatement) : Result
    {
        return $this->_dbConnection
            ->executeQuery("SELECT ". implode(", ", $columns). " FROM ". $tableName.
                " WHERE ". $whereStatement);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateVersion(int $id, string $version) : void
    {
        $this->_dbConnection
            ->executeQuery("UPDATE tl_release_stages SET version = ? WHERE id = ?", [$version, $id]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function downloadFromDatabase(string $testStageDatabaseName) : array
    {
        $tableNames = $this->getTableNamesFromDatabase($testStageDatabaseName);
        $table = array();
        foreach ($tableNames as $tableName)
        {
            $tableContent = $this->_dbConnection
                ->executeQuery("SELECT * FROM ?", [$tableName]);
            $table[] = array($tableName, $tableContent);
        }
        return $table;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function getTableNamesFromDatabase(string $testStageDatabaseName) : array
    {
        $tables = $this->_dbConnection
            ->executeQuery("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = \"?\"
                                                   AND TABLE_NAME LIKE \"tl_%\"", [$testStageDatabaseName]);
        $ignoredTables = $this->getIgnoredTables();
        $tableNames = array();

        var_dump();
        die;
        while ($tables->next()) {
            $tableName = $tables->TABLE_NAME;
            if (!in_array($tableName, $ignoredTables)) {
                $tableNames[] = $tableName;
            }
        }
        return $tableNames;
    }

    private function getIgnoredTables() : array
    {
        return $this->_ioLogic->getDatabaseIgnoredTablesConfiguration();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function loadHexById(string $column, string $tableName, string $id) : Result
    {
        return $this->_dbConnection
            ->executeQuery("SELECT hex(?) FROM ? WHERE id = ?", [$column, $tableName, $id]);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function checkForDeletedFilesInTlLogTable() : Result
    {
        return $this->_dbConnection
            ->executeQuery("SELECT text FROM tl_log WHERE text LIKE 'File or folder % has been deleted'");
    }
}
