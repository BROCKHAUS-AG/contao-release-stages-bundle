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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Release;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FileSystem\FileSystemDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Release\ReleaseDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseDeployer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileSystem\FileSystemDeployer;
use Exception;

class ReleaseDeployer
{
    private FileSystemDeployer $_fileSystemDeployer;
    private DatabaseDeployer $_databaseDeployer;

    public function __construct(FileSystemDeployer $fileSystemDeployer, DatabaseDeployer $databaseDeployer)
    {
        $this->_fileSystemDeployer = $fileSystemDeployer;
        $this->_databaseDeployer = $databaseDeployer;
    }

    /**
     * @throws ReleaseDeployment
     */
    public function deploy(): string
    {
        try {
            $debugMessage = $this->deployNewRelease() . "\n";
        } catch (Exception $e) {
           throw new ReleaseDeployment("Failed to deploy new release: $e");
        } finally {
            return $debugMessage;
        }
    }

    /**
     * @throws FileSystemDeployment
     * @throws SSHConnection
     * @throws DatabaseDeployment
     * @throws Exception
     */
    private function deployNewRelease(): string
    {
        $debugMessage = $this->_fileSystemDeployer->deploy() . "\n";
        $debugMessage .= $this->_databaseDeployer->deploy() . "\n";
        return $debugMessage;
    }
}
