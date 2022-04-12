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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning;

use BrockhausAg\ContaoReleaseStagesBundle\Logger\Log;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Version\Version;
use Exception;

class VersioningLogic {
    private DatabaseLogic $_databaseLogic;
    private Log $_log;

    public function __construct(DatabaseLogic $databaseLogic, Log $log)
    {
        $this->_databaseLogic = $databaseLogic;
        $this->_log = $log;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function setNewVersionAutomatically(): void
    {
        try {
            $latestVersion = $this->_databaseLogic->getLatestReleaseVersion();
        } catch (Exception $e) {
            $latestVersion = $this->createDummyVersion();
        }
        $this->createAndUpdateToNewVersion($latestVersion);
    }

    private function createDummyVersion(): Version
    {
        return new Version(0, "majorRelease", "0.0");
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function createAndUpdateToNewVersion(Version $latestVersion)
    {
        $versionNumber = $this->createVersionNumber($latestVersion);
        $this->_databaseLogic->updateVersion($versionNumber);
    }


    private function createVersionNumber(Version $latestVersion) : string
    {
        $splitVersion = explode(".", $latestVersion->getVersion());
        if (strcmp($latestVersion->getKindOfRelease(), "release") != 0) {
            return $this->createRelease($splitVersion);
        }
        return $this->createMajorRelease($splitVersion);
    }

    private function createRelease(array $version) : string
    {
        $newVersion = $version[0]. ".". intval($version[1]+1);
        $this->_log->info("A new release (version ". $newVersion. ") has been requested");
        return $newVersion;
    }

    private function createMajorRelease(array $version) : string
    {
        $newVersion = intval($version[0]+1). ".0";
        $this->_log->info("A new major releases (version ". $newVersion. ") has been requested");
        return $newVersion;
    }
}
