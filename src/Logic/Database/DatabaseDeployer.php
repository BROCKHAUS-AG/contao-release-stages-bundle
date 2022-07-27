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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Database;

use BrockhausAg\ContaoReleaseStagesBundle\Constants\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsProdStage;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsTestStage;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\Poll;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\PollTimeout;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Extractor;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\RemoteFilePoller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;
use Exception;

class DatabaseDeployer
{
    private SSHConnector $_sshConnection;
    private Extractor $_extractor;
    private Poller $_poller;
    private Config $_config;

    public function __construct(SSHConnector $sshConnection, Extractor $extractor, RemoteFilePoller $poller,
                                Config $config)
    {
        $this->_sshConnection = $sshConnection;
        $this->_extractor = $extractor;
        $this->_poller = $poller;
        $this->_config = $config;
    }

    /**
     * @throws SSHConnection
     * @throws DatabaseDeployment
     */
    public function deploy(): void
    {
        $runner = $this->_sshConnection->connect();
        try {
            $path = $this->_config->getFileServerConfiguration()->getPath();
            $this->extract($runner, $path);
            $this->migrate($runner, $path);
        } catch (Exception $e) {
            throw new DatabaseDeployment("Couldn't deploy database: $e");
        }finally {
            $this->_sshConnection->disconnect();
        }
    }

    private function extract(SSHRunner $runner, string $path): void
    {
        $file = $this->getFilePath($runner, $path);
        $extractedPath = $path. ConstantsProdStage::DATABASE_EXTRACTED_MIGRATION_DIRECTORY;
        $this->_extractor->extract($runner, $file, $extractedPath,
            ConstantsProdStage::DATABASE_MIGRATION_FILE_COMPRESSED, $path);
    }

    private function getFilePath(SSHRunner $runner, string $path): string
    {
        return $runner->getPathOfLatestFileWithPattern($path. str_replace(
            Constants::FILE_TIMESTAMP_PATTERN, "*",
            ConstantsProdStage::DATABASE_MIGRATION_FILE));
    }

    /**
     * @throws Poll
     * @throws PollTimeout
     */
    private function migrate(SSHRunner $runner, string $path)
    {
        $scriptPath = $path. ConstantsProdStage::MIGRATE_DATABASE_SCRIPT;
        $tags = $this->createTagsToMigrate($path);
        $runner->executeBackgroundScript($scriptPath, $tags);
        $this->_poller->pollFile($path. ConstantsProdStage::MIGRATE_DATABASE_POLL_FILE);
    }

    private function createTagsToMigrate(string $path): array
    {
        $config = $this->_config->getDatabaseConfiguration();
        $username = $config->getUsername();
        $password = $config->getPassword();
        $host = $config->getServer();
        $database = $config->getName();

        return array(
            "-u \"$username\"",
            "-p \"$password\"",
            "-h \"$host\"",
            "-d \"$database\"",
            "-f \"$path". ConstantsTestStage::DATABASE_MIGRATION_FILE. "\""
        );
    }
}
