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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Database;

use BrockhausAg\ContaoReleaseStagesBundle\Constants\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsProdStage;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseRollback;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\RemoteFilePoller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Rollbacker;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use Exception;

class DatabaseRollbacker
{
    private Rollbacker $_rollbacker;
    private SSHConnector $_sshConnection;
    private Poller $_poller;
    private Config $_config;

    public function __construct(Rollbacker $rollbacker, SSHConnector $sshConnection, RemoteFilePoller $poller,
                                Config $config)
    {
        $this->_rollbacker = $rollbacker;
        $this->_sshConnection = $sshConnection;
        $this->_poller = $poller;
        $this->_config = $config;
    }

    /**
     * @throws DatabaseRollback
     */
    public function rollback(): void
    {
        try {
            $path = $this->_config->getFileServerConfiguration()->getPath();
            $extractTo = $path. ConstantsProdStage::DATABASE_EXTRACTED_MIGRATION_DIRECTORY;
            $this->_rollbacker->rollback($extractTo,
                ConstantsProdStage::DATABASE_ROLLBACK_FILE_NAME,
                $path, $path. ConstantsProdStage::BACKUP_DATABASE_PATH,
                Constants::FILE_TIMESTAMP_PATTERN);
            $this->uploadBackup();

        } catch (Exception $e) {
            throw new DatabaseRollback("Couldn't rollback database: $e");
        }
    }

    private function uploadBackup()
    {
    }
}
