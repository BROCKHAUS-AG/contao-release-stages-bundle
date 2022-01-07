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

DEFINE("PATH", "/var/www/html/contao/settings/brockhaus-ag/contao-release-stages-bundle/");
DEFINE("CONFIG_FILE", "config.json");

class IOLogic {
    private function checkIfFileExists(string $file)
    {
        if (!file_exists($file)) {
            $errorMessage = "File: \"". $file. "\" could not be found. Please create it!";
            echo $errorMessage;
            exit();
        }
    }

    private function loadJsonFileAndDecode(string $file) : ?array
    {
        $this->checkIfFileExists($file);
        $fileContent = file_get_contents($file);
        return json_decode($fileContent, true);
    }

    private function loadConfiguration() : array
    {
        return $this->loadJsonFileAndDecode(PATH. CONFIG_FILE);
    }

    public function loadDatabaseConfiguration() : array
    {
        return $this->loadConfiguration()["database"];
    }
}
