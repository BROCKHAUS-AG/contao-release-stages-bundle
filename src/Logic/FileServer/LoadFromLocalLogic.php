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

class LoadFromLocalLogic {
    private string $_path;

    public function __construct(string $path)
    {
        $this->_path = $path;
    }

    public function loadFromLocal() : array
    {
        $directoriesLayout = glob($this->_path. "layout*", GLOB_ONLYDIR);
        $directoriesLayout = array_reverse($directoriesLayout);
        return $this->getFilesWithTimestamp($this->_path, $directoriesLayout);
    }

    private function getFilesWithTimestamp(string $path, array $directoriesLayout) : array
    {
        $directories = glob($path. "*", GLOB_ONLYDIR);
        $files = glob($path. "*.{jpg,webp,png,svg,pdf,mp4}", GLOB_BRACE);

        $filesWithTimestamp = $this->loadFiles($files);

        return $this->loadDirectories($directories, $filesWithTimestamp, $directoriesLayout);
    }

    private function loadFiles(array $files) : array
    {
        $filesWithTimestamp = array();
        foreach ($files as $file)
        {
            $filesWithTimestamp[] = array("lastModified" => filemtime($file), "filename" => $file);
        }
        return $filesWithTimestamp;
    }

    private function loadDirectories(array $directories, array $filesWithTimestamp, array $directoriesLayout) : array
    {
        foreach ($directories as $directory)
        {
            if (strpos($directory, "layout") === false) {
                $filesWithTimestamp = $this->getFromDirectory($directory, $filesWithTimestamp, $directoriesLayout);
            }
        }
        return $filesWithTimestamp;
    }

    private function getFromDirectory(string $directory, array $filesWithTimestamp, array $directoriesLayout) : array
    {
        $filesFromDirectory = $this->getFilesWithTimestamp($directory. "/", $directoriesLayout);
        foreach ($filesFromDirectory as $fileFromDirectory)
        {
            $filesWithTimestamp[] = $fileFromDirectory;
        }
        return $filesWithTimestamp;
    }
}
