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

use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\File\ConfigNotFound;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecordCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ftp;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ssh;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemConfig;

class Config {
    private string $_contaoPath;
    private SystemConfig $_systemConfig;
    private Logger $_logger;

    public function __construct(string $contaoPath, SystemConfig $systemConfig, Logger $logger)
    {
        $this->_contaoPath = $contaoPath;
        $this->_systemConfig = $systemConfig;
        $this->_logger = $logger;
    }

    public function getPathToContaoFiles(): string
    {
        return $this->_contaoPath. "/files";
    }

    public function getDatabaseConfiguration(): Database
    {
        return $this->getConfig()->getDatabase();
    }

    public function getDatabaseIgnoredTablesConfiguration(): array
    {
        $ignoredTables = $this->getConfig()->getDatabase()->getIgnoredTables();
        array_push($ignoredTables, "tl_user", "tl_cron_job", Constants::DEPLOYMENT_TABLE);
        return $ignoredTables;
    }

    public function getDNSRecords(): DNSRecordCollection
    {
        return $this->getConfig()->getDnsRecords();
    }

    public function getWhereToCopy(): string
    {
        return $this->getConfig()->getCopyTo();
    }

    public function getFileServerConfiguration(): FileServer
    {
        return $this->getConfig()->getFileServer();
    }

    public function getFTPConfiguration(): Ftp
    {
        return $this->getFileServerConfiguration()->getFtp();
    }

    public function getSSHConfiguration(): Ssh
    {
        return $this->getFileServerConfiguration()->getSsh();
    }

    public function getLocalFileServerConfiguration(): Local
    {
        return $this->getConfig()->getLocal();
    }

    public function getMaxSpendTimeWhileCreatingRelease(): int
    {
        return $this->getConfig()->getMaxSpendTimeWhileCreatingRelease();
    }

    public function getFileFormats(): array
    {
        return $this->getConfig()->getFileFormats();
    }

    private function getConfig(): \BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config
    {
        try {
            return $this->_systemConfig->getConfig();
        } catch (ConfigNotFound $e) {
            $this->_logger->error($e->getMessage());
            die($e->getMessage());
        }
    }
}
