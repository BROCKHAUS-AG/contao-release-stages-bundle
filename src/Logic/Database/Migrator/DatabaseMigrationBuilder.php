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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Migrator;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseExecutionFailure;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DatabaseMigration;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseProd;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Database\TableInformationCollection;
use Throwable;

class DatabaseMigrationBuilder
{
    private Database $_databaseLogic;
    private DatabaseProd $_prodDatabaseLogic;
    private CreateTableCommandsMigrationBuilder $_createTableCommandsMigrationBuilder;
    private InsertCommandsMigrationBuilder $_insertCommandsMigrationBuilder;
    private DeleteCommandsMigrationBuilder $_deleteCommandsMigrationBuilder;

    public function __construct(Database $databaseLogic, DatabaseProd $prodDatabaseLogic,
                                CreateTableCommandsMigrationBuilder $createTableCommandsMigrationBuilder)
    {
        $this->_databaseLogic = $databaseLogic;
        $this->_prodDatabaseLogic = $prodDatabaseLogic;
        $this->_createTableCommandsMigrationBuilder = $createTableCommandsMigrationBuilder;
    }

    /**
     * @throws DatabaseMigration
     */
    public function migrate(): void
    {
        $this->createMigrationFile();
        $this->copyMigrationFileToProd();
        $this->runMigrationFile();
    }

    /**
     * @throws DatabaseMigration
     */
    private function createMigrationFile(): void
    {
        try {
            $tableInformationCollection = $this->_databaseLogic->getFullTableInformation();
            $createTableCommands = $this->buildCreateTableCommandsIfNotExists($tableInformationCollection);

            $this->checkForDeleteEntriesInTlLogTable();
        } catch (Throwable $e) {
            throw new DatabaseMigration("Couldn't create migration: $e");
        }
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function buildCreateTableCommandsIfNotExists(TableInformationCollection $tableInformationCollection): array
    {
        $commands = array();
        for ($x = 0; $x != $tableInformationCollection->getLength(); $x++)
        {
            $command = $this->buildCreateTableCommandIfNotExists($tableInformationCollection->getByIndex($x)->getName());
            if ($command != null) {
                $commands[] = $command;
            }
        }
        return $commands;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function buildCreateTableCommandIfNotExists(string $table): ?string
    {
        if (!$this->_prodDatabaseLogic->checkIfTableExists($table)) {
            $tableScheme = $this->_databaseLogic->getTableScheme($table);
            $command = $this->_createTableCommandsMigrationBuilder->build($table, $tableScheme);
        }
        return $command ?? null;
    }

    /**
     * @throws DatabaseExecutionFailure
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function checkForDeleteEntriesInTlLogTable(): void
    {
        $lastId = $lastId = $this->_prodDatabaseLogic->getLastIdFromTlLogTable();
        $res = $this->_databaseLogic->getRowsFromTlLogTableWhereIdIsBiggerThanIdAndTextIsLikeDeleteFrom($lastId);

        $deleteStatements = array();
        foreach ($res as $statement) {
            $deleteStatements[] = $statement["text"];
        }
        $this->_prodDatabaseLogic->executeCommands($deleteStatements);
    }


    private function copyMigrationFileToProd(): void
    {
    }

    private function runMigrationFile(): void
    {
    }
}
