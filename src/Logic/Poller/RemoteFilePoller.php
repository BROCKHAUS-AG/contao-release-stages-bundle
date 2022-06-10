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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\Poll;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\PollTimeout;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;

class RemoteFilePoller extends Poller
{
    private FTPConnector $_ftpConnector;

    public function __construct(FTPConnector $ftpConnector)
    {
        $this->_ftpConnector = $ftpConnector;
    }

    /**
     * @throws Poll
     * @throws PollTimeout
     *
     * Run 50 times all 500ms (25s) to check if fail or success file was created. If after 25s no success or fail file
     * is available, break polling with poll timeout exception
     */
    public function pollFile(string $filePath): void
    {
        try {
            $ftpRunner = $this->_ftpConnector->connect();
            $repetitions = 0;
            while ($repetitions < 50) {
                if ($ftpRunner->checkIfFileExists("$filePath.success")) {
                    return;
                }
                if ($ftpRunner->checkIfFileExists("$filePath.fail")) {
                    throw new Poll("Failed file \"$filePath.fail\" was created");
                }
                usleep(500000);
                $repetitions = $repetitions + 1;
            }
            throw new PollTimeout("Backup failed, timeout");
        }catch (FTPConnection $e) {
            throw new Poll("Couldn't poll: $e");
        }finally {
            $this->_ftpConnector->disconnect($ftpRunner->getConn());
        }
    }
}
