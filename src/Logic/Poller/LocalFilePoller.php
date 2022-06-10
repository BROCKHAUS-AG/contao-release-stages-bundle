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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\Poll;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\PollTimeout;

class LocalFilePoller extends Poller
{
    /**
     * @throws PollTimeout
     * @throws Poll
     *
     * Run 50 times all 500ms (25s) to check if fail or success file was created. If after 25s no success or fail file
     * is available, break polling with poll timeout exception
     */
    public function pollFile(string $filePath): void
    {
        $repetitions = 0;
        while ($repetitions < 50) {
            if ($this->checkIfFileExists("$filePath.success")) {
                return;
            }
            if ($this->checkIfFileExists("$filePath.fail")) {
                throw new Poll("Failed file \"$filePath.fail\" was created");
            }
            usleep(500000);
            $repetitions = $repetitions + 1;
        }
        throw new PollTimeout("Backup failed, timeout");
    }

    private function checkIfFileExists(string $file): bool
    {
        $result = exec("test -f $file && echo true");
        return $result == "true";
    }
}
