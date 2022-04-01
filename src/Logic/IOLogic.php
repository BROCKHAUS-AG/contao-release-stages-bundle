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

use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\ArrayOfDNSRecords;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\FileServer;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Local;
use BrockhausAg\ContaoReleaseStagesBundle\System\SystemConfig;

class IOLogic {
    private SystemConfig $_systemConfig;
    private string $_contaoPath;

    public function __construct(string $contaoPath, SystemConfig $systemConfig)
    {
        $this->_contaoPath = $contaoPath;
        $this->_systemConfig = $systemConfig;
    }

    public function loadPathToContaoFiles() : string
    {
        return $this->_contaoPath. "/files";
    }

    public function loadDatabaseConfiguration() : Database
    {
        return $this->getConfig()->getDatabase();
    }

    public function loadTestStageDatabaseName() : string
    {
        return $this->getConfig()->getDatabase()->getTestStageDatabaseName();
    }

    public function loadDatabaseIgnoredTablesConfiguration() : array
    {
        $ignoredTables = $this->getConfig()->getDatabase()->getIgnoredTables();
        array_push($ignoredTables, "tl_user", "tl_cron_job", "tl_release_stages");
        return $ignoredTables;
    }

    public function loadDNSRecords() : ArrayOfDNSRecords
    {
        return $this->getConfig()->getDnsRecords();
    }

    public function checkWhereToCopy() : string
    {
        return $this->getConfig()->getCopyTo();
    }

    public function loadFileServerConfiguration() : FileServer
    {
        return $this->getConfig()->getFileServer();
    }

    public function loadLocalFileServerConfiguration() : Local
    {
        return $this->getConfig()->getLocal();
    }

    public function loadFileFormats() : array
    {
        return $this->getConfig()->getFileFormats();
    }

    private function getConfig() : Config
    {
        return $this->_systemConfig->getConfig();
    }
}
