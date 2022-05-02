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

namespace BrockhausAg\ContaoReleaseStagesBundle\System;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\DatabaseCouldNotCreateTable;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FileNotFound;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Mapper\Config\MapConfig;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Config\Config;

DEFINE("SETTINGS_PATH", "/settings/brockhaus-ag/contao-release-stages-bundle");
DEFINE("CONFIG_FILE", "/config.json");
class SystemConfig
{
    private string $_contaoPath;
    private Log $_log;
    private MapConfig $_mapConfig;
    protected Config $_config;

    public function __construct(string $contaoPath, MapConfig $mapConfig, Log $log)
    {
        $this->_contaoPath = $contaoPath;
        $this->_log = $log;
        $this->_mapConfig = $mapConfig;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function loadConfig(): void
    {
        $this->_config = $this->loadJsonFileAndDecode();
    }

    public function loadJsonFileAndDecode(): Config
    {
        $file = $this->createPath();
        try {
            $this->checkIfFileExists($file);
        } catch (FileNotFound $e) {
            $this->_log->error($e->getMessage());
            die($e);
        }
        $fileContent = file_get_contents($file);
        return $this->_mapConfig->map(json_decode($fileContent));
    }

    /**
     * @throws FileNotFound
     */
    private function checkIfFileExists(string $file): void
    {
        if (!file_exists($file)) {
            $errorMessage = "File: \"". $file. "\" could not be found. Please create it!";
            $this->_log->error($errorMessage);
            throw new FileNotFound($errorMessage);
        }
    }

    private function createPath(): string
    {
        return $this->_contaoPath. SETTINGS_PATH. CONFIG_FILE;
    }

    /**
     * @throws DatabaseCouldNotCreateTable
     */
    public function getConfig(): Config
    {
        if (!isset($this->_config)) {
            throw new DatabaseCouldNotCreateTable("Could not get config. Config was maybe not instantiated from
                config file");
        }
        return $this->_config;
    }
}
