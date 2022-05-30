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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FTP\FTPConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;

class Poller {
    private FTPConnector $_ftpConnector;

    public function __construct(FTPConnector $ftpConnector)
    {
        $this->_ftpConnector = $ftpConnector;
    }

    /**
     * @throws Poll
     */
    public function pollFile(string $filePath): void
    {
        try {
            $ftpRunner = $this->_ftpConnector->connect();
            $x = 0;
            while ($x < 50) {
                if ($ftpRunner->checkIfFileExists("$filePath.success")) {
                    return;
                }
                if ($ftpRunner->checkIfFileExists("$filePath.fail")) {
                    throw new Poll("Backup failed, file \"$filePath.fail\" was created");
                }
                usleep(500);
                $x = $x + 1;
            }
            throw new Poll("Backup failed, timeout");
        }catch (FTPConnection $e) {
            throw new Poll("Couldn't poll: $e");
        }finally {
            $this->_ftpConnector->disconnect($ftpRunner->getConn());
        }
    }
}
