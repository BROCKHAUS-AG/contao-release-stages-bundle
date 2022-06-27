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
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Compress;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\BuildDatabaseMigration;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\CreateTableMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\InsertMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Compressor;
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
    private string $_filePath;
    private string $_path;
    private FTPConnector $_ftpConnector;
    private Config $_config;
    private Compressor $_compressor;
    private IO $_io;

    public function __construct(CreateTableStatementsMigrationBuilder $createTableStatementsMigrationBuilder,
                                InsertStatementsMigrationBuilder $insertStatementsMigrationBuilder, string $path,
                                FTPConnector $ftpConnector, Config $config, Compressor $compressor)
    {
        $this->_createTableStatementsMigrationBuilder = $createTableStatementsMigrationBuilder;
        $this->_insertStatementsMigrationBuilder = $insertStatementsMigrationBuilder;
        $this->_path = $path;
        $this->_ftpConnector = $ftpConnector;
        $this->_config = $config;
        $this->_compressor = $compressor;
        $this->_filePath = $path. Constants::DATABASE_MIGRATION_FILE;
        $this->_io = new IO($this->_filePath);
    }

    /**
     * @throws BuildDatabaseMigration
     */
    public function buildAndCopy(): void
    {
        $this->createMigrationFile();
        $this->copyMigrationFileToProd();
    }

    /**
     * @throws BuildDatabaseMigration
     */
    private function createMigrationFile(): void
    {
        try {
            $statements = $this->buildStatements();
            $this->saveStatementsToMigrationFile($statements);
            $this->compressMigrationFile();
        } catch (Throwable $e) {
            throw new BuildDatabaseMigration("Couldn't build migration: $e");
        }
    }

    /**
     * @throws InsertMigrationBuilder
     * @throws CreateTableMigrationBuilder
     */
    private function buildStatements(): array
    {
        $createTableStatements = $this->_createTableStatementsMigrationBuilder->build();
        $insertStatements = $this->_insertStatementsMigrationBuilder->build();
        return $this->combineStatements($createTableStatements, $insertStatements);
    }

    private function combineStatements(array $createTableStatements, array $insertStatements): array
    {
        return array_merge($createTableStatements, $insertStatements);
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
     * @throws Compress
     */
    private function compressMigrationFile(): void
    {
        $migrationFile = $this->_path. Constants::MIGRATION_DIRECTORY;
        $this->_compressor->compressFile($this->_filePath, $migrationFile, Constants::DATABASE_MIGRATION_FILE_COMPRESSED);
    }

    /**
     * @throws BuildDatabaseMigration
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
            throw new BuildDatabaseMigration("Couldn't copy migration file: $e");
        }
    }

    private function buildFile(string $fileServerConfigurationPath): File
    {
        $fileProd = $this->buildFileProdPath($fileServerConfigurationPath);
        $fileLocal = $this->_path. Constants::MIGRATION_DIRECTORY. "/". Constants::DATABASE_MIGRATION_FILE_COMPRESSED. ".tar.gz";
        return new File($fileLocal, $fileProd);
    }

    private function buildFileProdPath(string $fileServerConfigurationPath): string
    {
        $fileProd = $fileServerConfigurationPath. Constants::DATABASE_MIGRATION_FILE_PROD;
        return str_replace("%timestamp%", (string)time(), $fileProd);
    }
}
