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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\DatabaseDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FileSystem\FileSystemDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\ReleaseDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseDeployer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileSystem\FileSystemDeployer;
use Exception;

class ReleaseRollbacker
{
    public function rollback(): void
    {

    }
}
