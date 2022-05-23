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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCopy;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPCreateDirectory;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;

abstract class Runner {
    /**
     * @throws FTPCreateDirectory
     */
    public abstract function createDirectory(string $directory): void;

    public abstract function getLastModifiedTimeFromFile(string $file): int;

    /**
     * @throws FTPCopy
     */
    public abstract function copy(File $file): void;

    /**
     * @throws FTPCopy
     */
    public abstract function update(File $file): void;

    public abstract function delete(string $file, string $path): void;
}
