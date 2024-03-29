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

use BrockhausAg\ContaoReleaseStagesBundle\Constants\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\File\ConfigNotFound;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecordCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Ftp;
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

    public function getProdDatabaseConfiguration(): Database
    {
        return $this->getConfig()->getProdDatabase();
    }

    public function getTestDatabaseConfiguration(): Database
    {
        return $this->getConfig()->getTestDatabase();
    }

    public function getDatabaseIgnoredTablesConfiguration(): array
    {
        $ignoredTables = $this->getConfig()->getProdDatabase()->getIgnoredTables();
        array_push($ignoredTables, "tl_user", "tl_cron_job", Constants::DEPLOYMENT_TABLE, "tl_search_index", "tl_search");
        return $ignoredTables;
    }

    public function getDNSRecords(): DNSRecordCollection
    {
        return $this->getConfig()->getDnsRecords();
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

    public function getMaxSpendTimeWhileCreatingRelease(): int
    {
        return $this->getConfig()->getMaxSpendTimeWhileCreatingRelease();
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
