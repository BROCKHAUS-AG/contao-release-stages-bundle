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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic;

use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Compress;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\LocalFilePoller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use Exception;

class Compressor {
    private string $_path;
    private Poller $_poller;

    public function __construct(string $path, LocalFilePoller $poller)
    {
            $this->_path = $path;
            $this->_poller = $poller;
    }

    /**
     * @throws Compress
     */
    public function compress(string $directory, string $compressedFile, string $name): void
    {
        try {
            $archiveScriptPath = $this->_path. Constants::CREATE_ARCHIVE_SCRIPT;
            shell_exec("bash $archiveScriptPath -f \"$directory\" -t \"$compressedFile\" -n \"$name\"");
            $this->_poller->pollFile( $this->_path. Constants::LOCAL_DIRECTORY. "/$name");
        }catch (Exception $e) {
            throw new Compress("Failed to compress to: \"$compressedFile\". $e");
        }
    }

    /**
     * @throws Compress
     */
    public function compressFile(string $file, string $compressedFile, string $name): void
    {
        try {
            $archiveScriptPath = $this->_path. Constants::CREATE_FILE_ARCHIVE_SCRIPT;
            shell_exec("bash $archiveScriptPath -f \"$file\" -t \"$compressedFile\" -n \"$name\"");
            $this->_poller->pollFile( $this->_path. Constants::LOCAL_DIRECTORY. "/$name");
        }catch (Exception $e) {
            throw new Compress("Failed to compress file to: \"$compressedFile\". $e");
        }
    }
}
