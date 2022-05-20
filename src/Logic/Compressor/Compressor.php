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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Compressor;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Compress;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use Exception;
use Phar;
use PharData;

class Compressor {
    private Log $_log;

    public function __construct(Log $log)
    {
        $this->_log = $log;
    }

    /**
     * @throws Compress
     */
    public function compress(array $files, string $compressedFile): void
    {
        try {
            $archive = new PharData($compressedFile);
            $archive = $this->addAllFilesToCompressedFile($files, $archive);
            $archive->compress(Phar::TAR);
        }catch (Exception $e) {
            $this->_log->error($e->getMessage());
            throw new Compress("Failed to compress to: \"$compressedFile\". $e");
        }
    }

    private function addAllFilesToCompressedFile(array $files, PharData $archive): PharData
    {
        foreach ($files as $file) {
            $archive->addFile($file);
        }
        return $archive;
    }
}
