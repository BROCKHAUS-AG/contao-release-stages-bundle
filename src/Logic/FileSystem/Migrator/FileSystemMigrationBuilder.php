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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FileSystem\Migrator;

use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Compress;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FileSystem\Migrator\BuildFileSystemMigration;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCopy;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCreateDirectory;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Compressor;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use Exception;

class FileSystemMigrationBuilder
{
    private string $_path;
    private Compressor $_compressor;
    private FTPConnector $_ftpConnector;
    private Config $_config;

    public function __construct(string $path, Compressor $compressor, FTPConnector $ftpConnector, Config $config)
    {
        $this->_path = $path;
        $this->_compressor = $compressor;
        $this->_ftpConnector = $ftpConnector;
        $this->_config = $config;
    }

    /**
     * @throws BuildFileSystemMigration
     */
    public function buildAndCopy(): void
    {
        $migrationFile = $this->_path. Constants::MIGRATION_DIRECTORY;
        try {
            $this->compressFiles($migrationFile);
            $this->copy($migrationFile);
        } catch (Exception $e) {
            throw new BuildFileSystemMigration("Couldn't create file system migration: $e");
        }
    }

    /**
     * @throws Compress
     */
    private function compressFiles(string $migrationFile): void
    {
        $directory = $this->_path. "/files/content";
        $this->_compressor->compress($directory, $migrationFile, Constants::FILE_SYSTEM_MIGRATION_FILE_NAME);
    }

    /**
     * @throws FTPCopy
     * @throws FTPConnection
     */
    private function copy(string $migrationFile): void
    {
        $runner = $this->_ftpConnector->connect();
        $prodPath = $this->buildPathForProd();
        $file = new File(
            "$migrationFile/". Constants::FILE_SYSTEM_MIGRATION_FILE_NAME. ".tar.gz",
            $prodPath
        );
        $runner->copy($file);
    }

    private function buildPathForProd(): string {
        $fileServerConfigurationPath = $this->_config->getFileServerConfiguration()->getPath();
        $fileProd = $fileServerConfigurationPath. Constants::FILE_SYSTEM_MIGRATION_FILE_PROD;
        return str_replace("%timestamp%", (string)time(), $fileProd);
    }
}
