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
use Contao\Backend;
use Contao\Database\Result;

class DatabaseLogic extends Backend
{
    public function getLastRows(int $count, array $columns, string $tableName) : Result
    {
        return $this->Database
            ->prepare("SELECT ". implode(", ", $columns). " FROM ". $tableName.
                " ORDER BY id DESC LIMIT ". $count)
            ->execute();
    }

    public function getLastRowsWithWhereStatement(array $columns, string $tableName, string $whereStatement) : Result
    {
        return $this->Database->prepare("SELECT ". implode(", ", $columns). " FROM ". $tableName.
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

    public function updateVersion(string $id, string $version) : void
    {
        $this->Database
            ->prepare("UPDATE tl_release_stages %s WHERE id=". $id)
            ->set(array("version" => $version))
            ->execute(1);
    }

    public function downloadFromDatabase() : array
    {
        $tableNames = $this->getTableNamesFromDatabase();
        $table = array();
        foreach ($tableNames as $tableName)
        {
            $tableContent = $this->Database->prepare("SELECT * FROM ". $tableName)
                ->execute()
                ->fetchAllAssoc();
            $table[] = array($tableName, $tableContent);
        }
        return $table;
    }

    private function getTableNamesFromDatabase() : array
    {
        $tables = $this->Database->prepare("SHOW TABLES FROM contao WHERE Tables_in_contao LIKE \"tl_%\"")
            ->execute();
        $ignoredTables = $this->getIgnoredTables();
        $tableNames = array();
        while ($tables->next()) {
            $tableName = $tables->Tables_in_contao;
            if (!in_array($tableName, $ignoredTables)) {
                $tableNames[] = $tableName;
            }
        }
        return $tableNames;
    }

    private function getIgnoredTables() : array
    {
        $ioLogic = new IOLogic();
        return $ioLogic->loadDatabaseIgnoredTablesConfiguration();
    }

    public function loadHexById(string $column, string $tableName, string $id) : Result
    {
        return $this->Database->prepare("SELECT hex(". $column. ") FROM ".
            $tableName. " WHERE id = ".$id)
            ->execute(1);
    }
}
