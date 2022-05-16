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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer;

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\IO;
use BrockhausAg\ContaoReleaseStagesBundle\Model\FileCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;

class LocalLoader {
    private IO $_ioLogic;
    private Log $_log;
    protected string $_path;
    protected string $_prodPath;

    public function __construct(IO $ioLogic, Log $log, string $path, string $prodPath)
    {
        $this->_ioLogic = $ioLogic;
        $this->_log = $log;
        $this->_path = $path;
        $this->_prodPath = $prodPath;
    }

    public function loadFromLocal() : FileCollection
    {
        $directoriesLayout = glob($this->_path. "layout*", GLOB_ONLYDIR);
        $directoriesLayout = array_reverse($directoriesLayout);
        return $this->getFilesWithTimestamp($this->_path, $directoriesLayout);
    }

    private function getFilesWithTimestamp(string $path, array $directoriesLayout) : FileCollection
    {
        $fileFormats = $this->_ioLogic->getFileFormats();
        $fileFormatsAsString = implode(",", $fileFormats);
        $directories = glob($path. "*", GLOB_ONLYDIR);

        $files = glob($path. "*.{". $fileFormatsAsString. "}", GLOB_BRACE);

        $filesWithTimestamp = $this->loadFiles($files);

        return $this->loadDirectories($directories, $filesWithTimestamp, $directoriesLayout);
    }

    private function loadFiles(array $files) : FileCollection
    {
        $filesWithTimestamp = new FileCollection();
        foreach ($files as $file)
        {
            $prodPathFile = $this->changePathToProdPath($file);
            $file = new File($file, $prodPathFile);
            $filesWithTimestamp->add($file);
        }
        return $filesWithTimestamp;
    }

    public function getTimestampFromFile(string $file): int
    {
        return filemtime($file);
    }

    private function changePathToProdPath(string $file) : string
    {
        return str_replace($this->_path, $this->_prodPath, $file);
    }

    private function loadDirectories(array $directories, FileCollection $filesWithTimestamp,
                                     array $directoriesLayout) : FileCollection
    {
        foreach ($directories as $directory)
        {
            if (strpos($directory, "layout") === false) {
                $filesWithTimestamp = $this->getFromDirectory($directory, $filesWithTimestamp, $directoriesLayout);
            }
        }
        return $filesWithTimestamp;
    }

    private function getFromDirectory(string $directory, FileCollection $filesWithTimestamp,
                                      array  $directoriesLayout) : FileCollection
    {
        $filesFromDirectory = $this->getFilesWithTimestamp($directory. "/", $directoriesLayout);
        foreach ($filesFromDirectory as $fileFromDirectory)
        {
            $filesWithTimestamp->add($fileFromDirectory);
        }
        return $filesWithTimestamp;
    }
}
