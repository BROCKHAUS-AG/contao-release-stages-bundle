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

use BrockhausAg\ContaoReleaseStagesBundle\DeploymentState;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseQueryEmptyResult;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Validation;
use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Logger\Logger;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use BrockhausAg\ContaoReleaseStagesBundle\Model\Version\Version;
use Doctrine\DBAL\Driver\Exception;

class Versioning {
    private Database $database;
    private Logger $_logger;

    public function __construct(Database $database, Logger $logger)
    {
        $this->database = $database;
        $this->_logger = $logger;
    }


    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws Validation
     * @throws DatabaseQueryEmptyResult
     */
    public function generateNewVersionNumber(int $id): void
    {
        if ($this->database->hasTableOneRow(Constants::DEPLOYMENT_TABLE)) {
            $latestVersion = $this->createDummyVersion();
        }else {
            $latestVersion = $this->database->getLatestReleaseVersion();
        }
        $this->createAndUpdateToNewVersion($id, $latestVersion);
    }

    /**
     * @throws Validation
     */
    private function createDummyVersion(): Version
    {
        return new Version(0, "majorRelease", "0.0", DeploymentState::PENDING);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function createAndUpdateToNewVersion(int $id, Version $latestVersion): void
    {
        $versionNumber = $this->createVersionNumber($latestVersion, "release");
        $this->database->updateVersion($id, $versionNumber);
    }

    public function createVersionNumber(Version $latestVersion, string $kindOfNewVersion): string
    {
        $splitVersion = explode(".", $latestVersion->getVersion());
        if ($kindOfNewVersion == "release") {
            return $this->createRelease($splitVersion);
        }
        return $this->createMajorRelease($splitVersion);
    }

    private function createRelease(array $version): string
    {
        $newVersion = $version[0]. ".". intval($version[1]+1);
        $this->_logger->info("A new release (version ". $newVersion. ") has been requested");
        return $newVersion;
    }

    private function createMajorRelease(array $version): string
    {
        $newVersion = intval($version[0]+1). ".0";
        $this->_logger->info("A new major releases (version ". $newVersion. ") has been requested");
        return $newVersion;
    }
}
