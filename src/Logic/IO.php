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

    public function append(string $data): void
    {
        $file = fopen($this->filePath, "w+");
        fwrite($file, $data);
        fclose($file);
    }

    public function read(): string
    {
        $file = fopen($this->filePath, "w+");
        $data = fread($file, filesize($this->filePath));
        fclose($file);
        return $data;
    }
}
