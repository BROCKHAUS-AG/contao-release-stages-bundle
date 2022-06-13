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

class IO {
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function write(string $data): void
    {
        $this->createDirectoryIfNotExists();
        file_put_contents($this->filePath, $data);
    }

    private function createDirectoryIfNotExists(): void
    {
        $directory = substr($this->filePath, 0, - strlen(substr(strrchr($this->filePath,'/'), 1)));
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    public function read(): string
    {
        $file = fopen($this->filePath, "w+");
        $data = fread($file, filesize($this->filePath));
        fclose($file);
        return $data;
    }
}
