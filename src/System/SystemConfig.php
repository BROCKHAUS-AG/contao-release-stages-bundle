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
    private Config $_config;

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

    private function loadJsonFileAndDecode(): Config
    {
        $file = $this->createPath();
        $this->checkIfFileExists($file);
        $fileContent = file_get_contents($file);
        return $this->_mapConfig->map(json_decode($fileContent));
    }

    private function checkIfFileExists(string $file): void
    {
        if (!file_exists($file)) {
            $errorMessage = "File: \"". $file. "\" could not be found. Please create it!";
            $this->_log->error($errorMessage);
            exit($errorMessage);
        }
    }

    private function createPath(): string
    {
        return $this->_contaoPath. SETTINGS_PATH. CONFIG_FILE;
    }

    public function getConfig(): Config
    {
        return $this->_config;
    }
}
