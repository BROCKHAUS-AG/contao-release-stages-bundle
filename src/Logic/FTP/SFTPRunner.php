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
use Doctrine\ORM\Cache\Exception\FeatureNotImplemented;

class SFTPRunner extends Runner {

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
        throw new FeatureNotImplemented();
    }

    /**
     * @throws FTPCopy
     */
    public function update(File $file): void
    {
        throw new FeatureNotImplemented();
    }

    public function delete(string $file, string $path): void
    {
        throw new FeatureNotImplemented();
    }

    private function checkIfDirectoryExists(string $path): bool
    {
        if ($this->_conn->chdir($path)) {
            return true;
        }
        return false;
    }

    private function checkIfFileExists(string $file): bool
    {
        $fileName = substr($file, strrpos($file, '/') + 1);
        $path = str_replace($fileName, "", $file);
        $files = ftp_nlist($this->_conn, $path);
        if (!$files) {
            return false;
        }
        return in_array($file, $files);
    }

    /**
     * @throws FTPCreateDirectory
     */
    private function mkdir(string $directory): void
    {
        $this->_conn->mkdir($directory);
        /*if(!ftp_mkdir($this->_conn, $directory)){
            throw new FTPCreateDirectory("Couldn't create directory \"$directory\". Maybe permissions are invalid.");
        }*/
    }

    private function repairDirectoryPermission(string $directory): void
    {
        $this->changePermission($directory, 0755);
    }

    /**
     * @throws FTPCopy
     */
    private function put(string $serverPath, string $path): void
    {
        /*if (@ftp_put($this->_conn, $serverPath, $path, FTP_ASCII)) {
            $this->repairPermission($serverPath);
        }else {
            throw new FTPCopy("Couldn't put file to \"$serverPath\" from \"$path\"");
        }*/
    }

    private function repairPermission(string $prodPath): void
    {
        $this->changePermission($prodPath, 0644);
    }

    private function changePermission(string $directory, int $permission): void
    {
        // ftp_chmod($this->_conn, $permission, $directory);
    }
}
