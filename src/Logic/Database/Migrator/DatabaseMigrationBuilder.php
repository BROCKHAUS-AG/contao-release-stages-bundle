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
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
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
    private Database $_testDatabaseConfig;

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
        $this->_testDatabaseConfig = $this->_config->getTestDatabaseConfiguration();
    }

    /**
     * @throws BuildDatabaseMigration
     * @throws Compress
     */
    public function buildAndCopy(): string
    {
        $debugMessage = $this->createMigrationFile() . "\n";
        $debugMessage .= $this->compressMigrationFile() . "\n";
        $debugMessage .= $this->copyMigrationFileToProd() . "\n";
        return $debugMessage;
    }

    /**
     * @throws BuildDatabaseMigration|Exception
     */
    private function createMigrationFile(): string
    {
        $ignoredTables = trim($this->getIgnoreTablesAsString());
        $debugMessage = date("H:i:s:u") . " migration ignore tables: " . $ignoredTables . "\n";
        $exitCode = shell_exec("bash " . $this->_path . ConstantsTestStage::BACKUP_LOCAL_DATABASE . " -i'".$ignoredTables."' -u'".$this->_testDatabaseConfig->getUsername()."' -p'".$this->_testDatabaseConfig->getPassword()."' -h'".$this->_testDatabaseConfig->getServer()."' -P'".$this->_testDatabaseConfig->getPort()."' -d'".$this->_testDatabaseConfig->getName()."' -t'" . $this->_path . ConstantsTestStage::DATABASE_MIGRATION_DIRECTORY . "' 2>&1; echo $?");
        if($exitCode != 0 && $exitCode) {
            throw new Exception("Failed to create local database backup. Output: $exitCode");
        } else {
            $debugMessage .= date("H:i:s:u") . " backuped local database \n";
        }
        return $debugMessage;
    }

    private function getIgnoreTablesAsString() : string
    {
        $ignoredTables = $this->_config->getDatabaseIgnoredTablesConfiguration();
        $formattedIgnoredTables = "";
        foreach($ignoredTables as $ignoredTable) {
            $formattedIgnoredTables = $formattedIgnoredTables . "--ignore-table=" . $this->_testDatabaseConfig->getName() . "." . $ignoredTable . " ";
        }
        return $formattedIgnoredTables;
    }

    /**
     * @throws Compress
     */
    private function compressMigrationFile(): string
    {
        $migrationFile = $this->_path . ConstantsTestStage::DATABASE_COMPRESSED_MIGRATION_DIRECTORY;
        $directory = $this->_path. ConstantsTestStage::DATABASE_MIGRATION_DIRECTORY;
        $name = ConstantsProdStage::DATABASE_MIGRATION_FILE_COMPRESSED;
        $this->_compressor->compress($directory, $migrationFile, $name);
        $debugMessage = date("H:i:s:u") . " compressed migration file\n";
        $debugMessage .= date("H:i:s:u") . " directory " . $directory . ", migrationfile " . $migrationFile . ", name" . $name . "\n";
        return $debugMessage;
    }

    /**
     * @throws BuildDatabaseMigration
     */
    private function copyMigrationFileToProd(): string
    {
        try {
            $runner = $this->_ftpConnector->connect();
            $debugMessage = date("H:i:s:u") . " connected to ftp ";
            $fileServerConfigurationPath = $this->_config->getFileServerConfiguration()->getRootPath();
            $file = $this->buildFile($fileServerConfigurationPath);
            $runner->copy($file);
            $debugMessage .= date("H:i:s:u") . " and copied file\n";
            $this->_ftpConnector->disconnect($runner->getConn());
            $debugMessage .= date("H:i:s:u") . " disconnected from ftp\n";
            return $debugMessage;
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
