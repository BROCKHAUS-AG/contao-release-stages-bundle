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

class LocalFilePoller extends Poller
{
    /**
     * Run 50 times all 500ms (25s) to check if fail or success file was created. If after 25s no success or fail file
     * is available, break polling with poll timeout exception
     */
    public function pollFile(string $filePath): void
    {
        // TODO: Implement pollFile() method.
    }
}
