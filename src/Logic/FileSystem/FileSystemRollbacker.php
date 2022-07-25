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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FileSystem;

use BrockhausAg\ContaoReleaseStagesBundle\Constants\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsProdStage;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsTestStage;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FileSystem\FileSystemRollback;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Extractor;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;
use Exception;

class FileSystemRollbacker
{
    private SSHConnector $_sshConnection;
    private Extractor $_extractor;
    private Config $_config;

    public function __construct(SSHConnector $sshConnection, Extractor $extractor,  Config $config)
    {
        $this->_sshConnection = $sshConnection;
        $this->_extractor = $extractor;
        $this->_config = $config;
    }

    /**
     * @throws SSHConnection
     * @throws FileSystemRollback
     */
    public function rollback(): void
    {
        $runner = $this->_sshConnection->connect();
        try {
            $path = $this->_config->getFileServerConfiguration()->getPath();
            $name = $this->getNameOfLatestBackup($runner, $path);
            $extractedPath = $path. ConstantsProdStage::FILE_SYSTEM_PATH;
            $this->_extractor->extract($runner, $name, $extractedPath,
                ConstantsProdStage::FILE_SYSTEM_ROLLBACK_FILE_NAME, $path);
        } catch (Exception $e) {
            throw new FileSystemRollback("Couldn't rollback file system: $e");
        }finally {
            $this->_sshConnection->disconnect();
        }
    }

    private function getNameOfLatestBackup(SSHRunner $runner, string $path): string
    {
        return $runner->getPathOfLatestFileWithPattern($path. str_replace(Constants::FILE_TIMESTAMP_PATTERN,
                "*", ConstantsProdStage::BACKUP_FILE_SYSTEM_PATH));
    }
}
