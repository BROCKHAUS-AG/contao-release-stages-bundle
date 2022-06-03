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
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DeleteMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\InsertMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use Exception;
use Throwable;

class DatabaseMigrationBuilder
{
    private CreateTableStatementsMigrationBuilder $_createTableStatementsMigrationBuilder;
    private InsertStatementsMigrationBuilder $_insertStatementsMigrationBuilder;
    private DeleteStatementsMigrationBuilder $_deleteStatementsMigrationBuilder;
    private string $_filePath;
    private FTPConnector $_ftpConnector;
    private Config $_config;
    private IO $_io;

    public function __construct(CreateTableStatementsMigrationBuilder $createTableStatementsMigrationBuilder,
                                InsertStatementsMigrationBuilder $insertStatementsMigrationBuilder,
                                DeleteStatementsMigrationBuilder $deleteStatementsMigrationBuilder, string $path,
                                FTPConnector $ftpConnector, Config $config)
    {
        $this->_createTableStatementsMigrationBuilder = $createTableStatementsMigrationBuilder;
        $this->_insertStatementsMigrationBuilder = $insertStatementsMigrationBuilder;
        $this->_deleteStatementsMigrationBuilder = $deleteStatementsMigrationBuilder;
        $this->_ftpConnector = $ftpConnector;
        $this->_filePath = $path. Constants::DATABASE_MIGRATION_FILE;
        $this->_config = $config;
        $this->_io = new IO($this->_filePath);
    }

    /**
     * @throws \BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DatabaseMigrationBuilder
     */
    public function buildAndCopy(): void
    {
        $this->createMigrationFile();
        $this->copyMigrationFileToProd();
    }

    /**
     * @throws \BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DatabaseMigrationBuilder
     */
    private function createMigrationFile(): void
    {
        try {
            $statements = $this->buildStatements();
            $this->saveStatementsToMigrationFile($statements);
        } catch (Throwable $e) {
            throw new \BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DatabaseMigrationBuilder("Couldn't build migration: $e");
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

    private function combineStatements(array $createTableStatements, array $insertStatements,
                                       array $deleteStatements): array
    {
        return array_merge($createTableStatements, $insertStatements, $deleteStatements);
    }

    private function saveStatementsToMigrationFile(array $statements): void
    {
        $convertedStatements = "";
        foreach ($statements as $statement)
        {
            $convertedStatements = $convertedStatements. $statement. "\n";
        }
        $this->_io->write($convertedStatements);
    }

    /**
     * @throws \BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DatabaseMigrationBuilder
     */
    private function copyMigrationFileToProd(): void
    {
        try {
            $runner = $this->_ftpConnector->connect();
            $fileServerConfigurationPath = $this->_config->getFileServerConfiguration()->getPath();
            $file = $this->buildFile($fileServerConfigurationPath);
            $runner->createDirectory($fileServerConfigurationPath. Constants::MIGRATION_DIRECTORY_PROD);
            $runner->copy($file);
            $this->_ftpConnector->disconnect($runner->getConn());
        }catch (Exception $e) {
            throw new \BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DatabaseMigrationBuilder("Couldn't copy migration file: $e");
        }
    }

    private function buildFile(string $fileServerConfigurationPath): File
    {
        $fileProd = $this->buildFileProdPath($fileServerConfigurationPath);
        return new File($this->_filePath, $fileProd);
    }

    private function buildFileProdPath(string $fileServerConfigurationPath): string
    {
        $fileProd = $fileServerConfigurationPath. Constants::DATABASE_MIGRATION_FILE_PROD;
        return str_replace("%timestamp%", (string)time(), $fileProd);
    }
}
