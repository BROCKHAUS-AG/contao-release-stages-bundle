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

use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsProdStage;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsTestStage;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Compress;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\BuildDatabaseMigration;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\CreateTableMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DeleteMigrationBuilder;
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
    private DeleteStatementsMigrationBuilder  $_deleteStatementsMigrationBuilder;
    private string $_path;
    private FTPConnector $_ftpConnector;
    private Config $_config;
    private Compressor $_compressor;
    private IO $_io;

    public function __construct(CreateTableStatementsMigrationBuilder $createTableStatementsMigrationBuilder,
                                InsertStatementsMigrationBuilder $insertStatementsMigrationBuilder,
                                DeleteStatementsMigrationBuilder $deleteStatementsMigrationBuilder, string $path,
                                FTPConnector $ftpConnector, Config $config, Compressor $compressor)
    {
        $this->_createTableStatementsMigrationBuilder = $createTableStatementsMigrationBuilder;
        $this->_insertStatementsMigrationBuilder = $insertStatementsMigrationBuilder;
        $this->_deleteStatementsMigrationBuilder = $deleteStatementsMigrationBuilder;
        $this->_path = $path;
        $this->_ftpConnector = $ftpConnector;
        $this->_config = $config;
        $this->_compressor = $compressor;
        $this->_io = new IO($path. ConstantsTestStage::DATABASE_MIGRATION_FILE);
    }

    /**
     * @throws BuildDatabaseMigration
     * @throws Compress
     */
    public function buildAndCopy(): void
    {
        $this->createMigrationFile();
        $this->compressMigrationFile();
        $this->copyMigrationFileToProd();
    }

    /**
     * @throws BuildDatabaseMigration
     */
    private function createMigrationFile(): void
    {
        $ignoredTables = $this->getIgnoreTablesAsString();
        $data = shell_exec("bash " . $this->_path . ConstantsTestStage::BACKUP_LOCAL_DATABASE . " -i'".$ignoredTables."' -u'root' -p'admin1234' -h'database' -P'3306' -d'contao' -t'" . $this->_path . ConstantsTestStage::DATABASE_MIGRATION_DIRECTORY . "' 2>&1");
        if($data) {
            throw new BuildDatabaseMigration("Exception while building migration on test stage: " . $data, -1);
        }
    }

    private function getIgnoreTablesAsString() : string
    {
        $ignoredTables = $this->_config->getDatabaseIgnoredTablesConfiguration();
        $formattedIgnoredTables = "";
        foreach($ignoredTables as $ignoredTable) {
            $formattedIgnoredTables = $formattedIgnoredTables . "--ignore-table=contao." . $ignoredTable . " ";
        }
        return $formattedIgnoredTables;
    }

    /**
     * @throws Compress
     */
    private function compressMigrationFile(): void
    {
        $migrationFile = $this->_path . ConstantsTestStage::DATABASE_COMPRESSED_MIGRATION_DIRECTORY;
        $directory = $this->_path. ConstantsTestStage::DATABASE_MIGRATION_DIRECTORY;
        $name = ConstantsProdStage::DATABASE_MIGRATION_FILE_COMPRESSED;
        $this->_compressor->compress($directory, $migrationFile, $name);
    }

    /**
     * @throws BuildDatabaseMigration
     */
    private function copyMigrationFileToProd(): void
    {
        try {
            $runner = $this->_ftpConnector->connect();
            $fileServerConfigurationPath = $this->_config->getFileServerConfiguration()->getRootPath();
            $file = $this->buildFile($fileServerConfigurationPath);
            $runner->copy($file);
            $this->_ftpConnector->disconnect($runner->getConn());
        }catch (Exception $e) {
            throw new BuildDatabaseMigration("Couldn't copy migration file: $e");
        }
    }

    private function buildFile(string $fileServerConfigurationPath): File
    {
        $fileProd = $this->buildFileProdPath($fileServerConfigurationPath);
        $fileLocal = $this->_path. ConstantsTestStage::DATABASE_COMPRESSED_MIGRATION_DIRECTORY. "/". ConstantsProdStage::DATABASE_MIGRATION_FILE_COMPRESSED. ".tar.gz";
        return new File($fileLocal, $fileProd);
    }

    private function buildFileProdPath(string $fileServerConfigurationPath): string
    {
        $fileProd = $fileServerConfigurationPath. ConstantsProdStage::DATABASE_MIGRATION_FILE;
        return str_replace("%timestamp%", (string)time(), $fileProd);
    }
}
