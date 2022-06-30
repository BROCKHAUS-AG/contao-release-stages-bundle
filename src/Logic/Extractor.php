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

use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsProdStage;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;

class Extractor
{
    private Poller $_poller;

    public function __construct(Poller $poller)
    {
        $this->_poller = $poller;
    }

    public function extract(SSHRunner $runner, string $toBeExtracted, string $extractedPath, string $pollName, string $path): void
    {
        $tags = $this->createTags($toBeExtracted, $extractedPath, $pollName);
        $runner->executeBackgroundScript($path. ConstantsProdStage::UN_ARCHIVE_SCRIPT, $tags);
        $this->_poller->pollFile($path. ConstantsProdStage::SCRIPT_DIRECTORY. "/$pollName");
    }

    private function createTags(string $toBeExtracted, string $extractedPath, string $pollName): array
    {
        return array(
            "-f \"$toBeExtracted\"",
            "-e \"$extractedPath\"",
            "-n \"$pollName\""
        );
    }
}
