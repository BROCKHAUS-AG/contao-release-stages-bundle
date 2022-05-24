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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Local;

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;

class FileServerLocalCopier {
    private Logger $_logger;

    public function __construct(Logger $logger)
    {
        $this->_logger = $logger;
    }

    public function createDirectory(string $directory) : void
    {
        if (!@mkdir($directory)) {
            $error = error_get_last();
            die("mkdir error: ". $error['message']);
        }
    }

    public function getLastModifiedTimeFromFile(string $file) : int
    {
        return filemtime($file);
    }

    public function copy(File $file) : void
    {
        if (!copy($file->getPath(), $file->getProdPath())) {
            $errors = error_get_last();
            echo "COPY ERROR: ".$errors['type'];
            echo "<br />\n".$errors['message'];
        }
    }

    public function delete(string $file, string $path) : void
    {
        $file = str_replace("files", "", $file);
        $file = $path. $file;
        if (file_exists($file)) {
            if (!unlink($file)) {
                $error = error_get_last();
                $errorMessage = "Failed to delete file: \"". $file. "\"</br>rm error: ". $error['message'];
                $this->_logger->error($errorMessage);
            }
        }
    }
}
