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

use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\RemoteFilePoller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;

class DatabaseRollbacker
{
    private SSHConnector $_sshConnection;
    private Poller $_poller;
    private Config $_config;

    public function __construct(SSHConnector $sshConnection, RemoteFilePoller $poller,  Config $config)
    {
        $this->_sshConnection = $sshConnection;
        $this->_poller = $poller;
        $this->_config = $config;
    }

    public function rollback(): void
    {

    }
}
