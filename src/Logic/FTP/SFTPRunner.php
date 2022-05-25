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
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPDelete;
use BrockhausAg\ContaoReleaseStagesBundle\Model\File;
use Doctrine\ORM\Cache\Exception\FeatureNotImplemented;
use phpseclib3\Net\SFTP;

class SFTPRunner extends AbstractFTPRunner {
    private SFTP $_sftp;

    public function __construct(SFTP $sftp)
    {
        $this->_sftp = $sftp;
    }

    /**
     * @return SFTP
     */
    public function getConn(): SFTP
    {
        return $this->_sftp;
    }

    /**
     * @throws FTPCreateDirectory
     */
    public function createDirectory(string $directory): void
    {
        if (!$this->checkIfDirectoryExists($directory)) {
            $this->mkdir($directory);
            $this->repairDirectoryPermission($directory);
        }
    }

    /**
     * @throws FeatureNotImplemented
     */
    public function getLastModifiedTimeFromFile(string $file): int
    {
        throw new FeatureNotImplemented();
    }

    /**
     * @throws FTPCopy
     */
    public function copy(File $file): void
    {
        $prodPath = $file->getProdPath();
        $path = $file->getPath();
        if ($this->checkIfFileExists($prodPath)) {
            return;
        }
        $this->put($prodPath, $path);
        $this->repairPermission($prodPath);
    }

    /**
     * @throws FeatureNotImplemented
     */
    public function update(File $file): void
    {
        throw new FeatureNotImplemented();
    }

    /**
     * @throws FTPDelete
     */
    public function delete(string $file, string $path): void
    {
        if (!$this->_sftp->delete($path. $file)) {
            throw new FTPDelete("Failed to delete \"$path$file\"");
        }
    }

    private function checkIfDirectoryExists(string $path): bool
    {
        return $this->_sftp->chdir($path);
    }

    private function checkIfFileExists(string $file): bool
    {
        return $this->_sftp->file_exists($file);
    }

    /**
     * @throws FTPCreateDirectory
     */
    private function mkdir(string $directory): void
    {
        if(!$this->_sftp->mkdir($directory)) {
            throw new FTPCreateDirectory("Couldn't create directory \"$directory\". Maybe permissions are invalid.");
        }
    }

    /**
     * @throws FTPCopy
     */
    private function put(string $serverPath, string $path): void
    {
        try {
            $this->_sftp->put($serverPath, $path, SFTP::SOURCE_LOCAL_FILE);
        }catch (\Exception $e) {
            throw new FTPCopy("Couldn't put file to \"$serverPath\" from \"$path\"");
        }
    }

    private function repairDirectoryPermission(string $directory): void
    {
        $this->changePermission($directory, 0755);
    }

    private function repairPermission(string $prodPath): void
    {
        $this->changePermission($prodPath, 0644);
    }

    private function changePermission(string $directory, int $permission): void
    {
        $this->_sftp->chmod($permission, $directory);
    }
}
