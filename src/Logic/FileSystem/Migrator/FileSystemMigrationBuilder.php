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
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Compressor;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;

class FileSystemMigrationBuilder
{
    private string $_path;
    private Compressor $_compressor;
    private FTPConnector $_ftpConnector;

    public function __construct(string $path, Compressor $compressor, FTPConnector $ftpConnector)
    {
        $this->_path = $path;
        $this->_compressor = $compressor;
        $this->_ftpConnector = $ftpConnector;
    }

    /**
     * @throws Compress
     */
    public function buildAndCopy(): void
    {
        $directory = $this->_path. "/files/content";
        $migrationFile = $this->_path. Constants::MIGRATION_DIRECTORY;
        $this->_compressor->compress($directory, $migrationFile, Constants::FILE_SYSTEM_MIGRATION_FILE_NAME);
    }
}
