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


use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use Exception;

class Poller {
    private SSHConnector $_sshConnection;

    public function __construct(SSHConnector $sshConnection)
    {
        $this->_sshConnection = $sshConnection;
    }

    /**
     * @throws SSHConnection
     * @throws Poll
     */
    public function pollFile(string $filePath): bool
    {
        $sshRunner = $this->_sshConnection->connect();
        try {
            $haveToPoll = true;
            $count = 0;
            $responseSuccess = $sshRunner->execute("test -f $filePath.success && echo \"true\"");
            $responseFail = $sshRunner->execute("test -f $filePath.fail && echo \"true\"");
            echo $responseSuccess;
            echo $responseFail;
            if ($responseSuccess == "true" || $responseFail == "true") {
                $haveToPoll = false;
            }
            return true;
        }catch (Exception $e) {
            throw new Poll("Couldn't poll: $e");
        }finally {
            $this->_sshConnection->disconnect();
            return false;
        }
    }
}
