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

namespace BrockhausAg\ContaoReleaseStagesBundle\EventListener\DataContainer;

use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\CopyToDatabaseLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\CopyToFileServerLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\VersioningLogic;
use Doctrine\DBAL\Exception;

class ReleaseStagesEventListener
{
    private VersioningLogic $_versioningLogic;
    private CopyToDatabaseLogic $_copyToDatabaseLogic;
    private CopyToFileServerLogic $_copyToFileServerLogic;

    public function __construct(VersioningLogic $versioningLogic, CopyToDatabaseLogic $copyToDatabaseLogic,
                                CopyToFileServerLogic $copyToFileServerLogic)
    {
        $this->_versioningLogic = $versioningLogic;
        $this->_copyToDatabaseLogic = $copyToDatabaseLogic;
        $this->_copyToFileServerLogic = $copyToFileServerLogic;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function onSubmitCallback() : void
    {
        $this->_versioningLogic->setNewVersionAutomatically();
        $this->_copyToDatabaseLogic->copyToDatabase();
        $this->_copyToFileServerLogic->copyToFileServer();
    }
}
