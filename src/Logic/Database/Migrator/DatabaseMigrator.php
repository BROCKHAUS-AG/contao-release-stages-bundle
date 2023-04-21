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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Migrator;

use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsProdStage;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\Poll;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\PollTimeout;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\RemoteFilePoller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;

class DatabaseMigrator
{
    private Poller $_poller;
    private Config $_config;
    private string $_path;

    public function __construct(RemoteFilePoller $poller, Config $config)
    {
        $this->_poller = $poller;
        $this->_config = $config;
        $this->_path = $this->_config->getFileServerConfiguration()->getRootPath();;
    }

    /**
     * @throws Poll
     * @throws PollTimeout
     */
    public function migrate(SSHRunner $runner, string $migrationFile)
    {
        $scriptPath = $this->_path. ConstantsProdStage::MIGRATE_DATABASE_SCRIPT;
        $tags = $this->createTagsToMigrate($migrationFile);
        $runner->executeBackgroundScript($scriptPath, $tags);
        $this->_poller->pollFile($this->_path. ConstantsProdStage::MIGRATE_DATABASE_POLL_FILE);
    }

    private function createTagsToMigrate(string $migrationFile): array
    {
        $config = $this->_config->getProdDatabaseConfiguration();
        $username = $config->getUsername();
        $password = $config->getPassword();
        $host = $config->getServer();
        $database = $config->getName();

        return array(
            "-u \"$username\"",
            "-p \"$password\"",
            "-h \"$host\"",
            "-d \"$database\"",
            "-f \"". $this->_path. $migrationFile. "\""
        );
    }
}
