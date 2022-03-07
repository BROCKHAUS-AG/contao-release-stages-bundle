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
use Psr\Log\LoggerInterface;

DEFINE("SETTINGS_PATH", "/html/contao/settings/brockhaus-ag/contao-release-stages-bundle/");
DEFINE("CONFIG_FILE", "config.json");

class IOLogic {
    private Config $config;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->config = $this->loadConfiguration();
    }

    public function loadPathToContaoFiles() : string
    {
        return $this->loadContaoPath(). "files";
    }

    public function loadDatabaseConfiguration() : Database
    {
        return $this->config->getDatabase();
    }

    public function loadTestStageDatabaseName() : string
    {
        return $this->config->getDatabase()->getTestStageDatabaseName();
    }

    public function loadDatabaseIgnoredTablesConfiguration() : array
    {
        $ignoredTables = $this->config->getDatabase()->getIgnoredTables();
        array_push($ignoredTables, "tl_user", "tl_cron_job", "tl_release_stages");
        return $ignoredTables;
    }

    public function loadDNSRecords() : ArrayOfDNSRecords
    {
        return $this->config->getDnsRecords();
    }

    public function checkWhereToCopy() : string
    {
        return $this->config->getCopyTo();
    }

    public function loadFileServerConfiguration() : FileServer
    {
        return $this->config->getFileServer();
    }

    public function loadLocalFileServerConfiguration() : Local
    {
        return $this->config->getLocal();
    }

    public function loadFileFormats() : array
    {
        return $this->config->getFileFormats();
    }

    private function loadJsonFileAndDecode(string $file) : Config
    {
        $this->checkIfFileExists($file);
        $fileContent = file_get_contents($file);
        return json_decode($fileContent, true);
    }

    private function checkIfFileExists(string $file)
    {
        if (!file_exists($file)) {
            $errorMessage = "File: \"". $file. "\" could not be found. Please create it!";
            $this->logger->info("Hello from release stages");
            echo $errorMessage;
            exit();
        }
    }

    private function loadConfiguration() : Config
    {
        return $this->loadJsonFileAndDecode(SETTINGS_PATH. CONFIG_FILE);
    }

    private function loadContaoPath() : string
    {
        return $this->config->getContaoPath();
    }
}
