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
use Contao\Database;
use Contao\Database\Result;
use Exception;

class DatabaseLogic
{
    private Database $_database;
    private IOLogic $_ioLogic;

    public function __construct(Database $database, IOLogic $ioLogic)
    {
        $this->_database = $database;
        $this->_ioLogic = $ioLogic;
    }

    /**
     * @throws Exception
     */
    public function getLatestReleaseVersion(): Version
    {
        $result = $this->_database
            ->prepare("SELECT id, version, kindOfRelease FROM tl_release_stages ORDER BY id DESC LIMIT 2")
            ->execute();
        if ($result->numRows == 0) {
            throw new Exception("no entry found");
        }

        return new Version(intval($result->id), $result->kindOfRelease, $result->version);
    }

    public function getLastRowsWithWhereStatement(array $columns, string $tableName, string $whereStatement) : Result
    {
        return $this->_database->prepare("SELECT ". implode(", ", $columns). " FROM ". $tableName.
            " WHERE ". $whereStatement)
            ->execute();
    }

    public function countRows($toCount) : int
    {
        $counter = 0;
        while ($toCount->next()) {
            $counter++;
        }
        return $counter;
    }

    public function updateVersion(int $id, string $version) : void
    {
        $this->_database
            ->prepare("UPDATE tl_release_stages %s WHERE id=%d")
            ->set(array("version" => $version, "id" => $id))
            ->execute(1);
    }

    public function downloadFromDatabase(string $testStageDatabaseName) : array
    {
        $tableNames = $this->getTableNamesFromDatabase($testStageDatabaseName);
        $table = array();
        foreach ($tableNames as $tableName)
        {
            $tableContent = $this->_database->prepare("SELECT * FROM ". $tableName)
                ->execute()
                ->fetchAllAssoc();
            $table[] = array($tableName, $tableContent);
        }
        return $table;
    }

    private function getTableNamesFromDatabase(string $testStageDatabaseName) : array
    {
        $tables = $this->_database->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES ".
            "WHERE TABLE_SCHEMA = \"". $testStageDatabaseName. "\" AND TABLE_NAME LIKE \"tl_%\";")
            ->execute();
        $ignoredTables = $this->getIgnoredTables();
        $tableNames = array();
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

    public function loadHexById(string $column, string $tableName, string $id) : Result
    {
        return $this->_database->prepare("SELECT hex(". $column. ") FROM ".
            $tableName. " WHERE id = ".$id)
            ->execute(1);
    }

    public function checkForDeletedFilesInTlLogTable() : Result
    {
        return $this->_database
            ->prepare("SELECT text FROM tl_log WHERE text LIKE 'File or folder % has been deleted'")
            ->execute();
    }
}
