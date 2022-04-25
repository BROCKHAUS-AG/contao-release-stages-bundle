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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\ConfigNotFoundException;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\DNSRecordCollection;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemConfig;

class IOLogic {
    private string $_contaoPath;
    private SystemConfig $_systemConfig;
    private Log $_log;

    public function __construct(string $contaoPath, SystemConfig $systemConfig, Log $log)
    {
        $this->_contaoPath = $contaoPath;
        $this->_systemConfig = $systemConfig;
        $this->_log = $log;
    }

    public function getPathToContaoFiles() : string
    {
        return $this->_contaoPath. "/files";
    }

    public function getDatabaseConfiguration() : Database
    {
        return $this->getConfig()->getDatabase();
    }

    public function getDatabaseIgnoredTablesConfiguration() : array
    {
        $ignoredTables = $this->getConfig()->getDatabase()->getIgnoredTables();
        array_push($ignoredTables, "tl_user", "tl_cron_job", "tl_release_stages");
        return $ignoredTables;
    }

    public function getDNSRecords() : DNSRecordCollection
    {
        return $this->getConfig()->getDnsRecords();
    }

    public function getWhereToCopy() : string
    {
        return $this->getConfig()->getCopyTo();
    }

    public function getFileServerConfiguration() : FileServer
    {
        return $this->getConfig()->getFileServer();
    }

    public function getLocalFileServerConfiguration() : Local
    {
        return $this->getConfig()->getLocal();
    }

    public function getFileFormats() : array
    {
        return $this->getConfig()->getFileFormats();
    }

    private function getConfig() : Config
    {
        try {
            return $this->_systemConfig->getConfig();
        } catch (ConfigNotFoundException $e) {
            $this->_log->error($e->getMessage());
            die($e->getMessage());
        }
    }
}
