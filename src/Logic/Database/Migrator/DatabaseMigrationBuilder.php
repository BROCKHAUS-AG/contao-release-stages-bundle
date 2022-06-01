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

use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\CreateTableMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DatabaseMigration;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DeleteMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\InsertMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use Throwable;

class DatabaseMigrationBuilder
{
    private CreateTableStatementsMigrationBuilder $_createTableStatementsMigrationBuilder;
    private InsertStatementsMigrationBuilder $_insertStatementsMigrationBuilder;
    private DeleteStatementsMigrationBuilder $_deleteStatementsMigrationBuilder;
    private IO $_io;

    public function __construct(CreateTableStatementsMigrationBuilder $createTableStatementsMigrationBuilder,
                                InsertStatementsMigrationBuilder $insertStatementsMigrationBuilder,
                                DeleteStatementsMigrationBuilder $deleteStatementsMigrationBuilder, string $path)
    {
        $this->_createTableStatementsMigrationBuilder = $createTableStatementsMigrationBuilder;
        $this->_insertStatementsMigrationBuilder = $insertStatementsMigrationBuilder;
        $this->_deleteStatementsMigrationBuilder = $deleteStatementsMigrationBuilder;
        $this->_io = new IO($path. Constants::DATABASE_MIGRATION_FILE);
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
            $statements = $this->buildStatements();
            $this->saveStatementsToMigrationFile($statements);
        } catch (Throwable $e) {
            throw new DatabaseMigration("Couldn't create migration: $e");
        }
    }

    /**
     * @throws InsertMigrationBuilder
     * @throws DeleteMigrationBuilder
     * @throws CreateTableMigrationBuilder
     */
    private function buildStatements(): array
    {
        $createTableStatements = $this->_createTableStatementsMigrationBuilder->build();
        $insertStatements = $this->_insertStatementsMigrationBuilder->build();
        $deleteStatements = $this->_deleteStatementsMigrationBuilder->build();
        return $this->combineStatements($createTableStatements, $insertStatements, $deleteStatements);
    }

    private function combineStatements(array $createTableStatements, array $insertStatements, array $deleteStatements): array
    {
        return array_merge($createTableStatements, $insertStatements, $deleteStatements);
    }

    private function saveStatementsToMigrationFile(array $statements): void
    {
        var_dump($statements);
        die;
    }

    private function copyMigrationFileToProd(): void
    {
    }

    private function runMigrationFile(): void
    {
    }
}
