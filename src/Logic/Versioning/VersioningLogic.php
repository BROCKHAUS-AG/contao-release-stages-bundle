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
        return new Version(0, "release", "1.0");
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function createAndUpdateToNewVersion(Version $latestVersion)
    {
        $versionNumber = $this->createVersionNumber($latestVersion);
        $this->_databaseLogic->updateVersion($latestVersion->getId()+1, $versionNumber);
    }


    private function createVersionNumber(Version $oldVersion) : string
    {
        $version = explode(".", $oldVersion->getVersion());
        if (strcmp($oldVersion->getKindOfRelease(), "release") == 0) {
            return $this->createRelease($version);
        }
        return $this->createMajorRelease($version);
    }

    private function createRelease(array $version) : string
    {
        $this->_log->info("A new release (version )". ($version[1]+1). " has been requested.");
        return $version[0]. ".". intval($version[1]+1);
    }

    private function createMajorRelease(array $version) : string
    {
        $this->_log->info("A new major releases (version )". ($version[0]+1). " has been requested:");
        return intval($version[0]+1). ".0";
    }
}
