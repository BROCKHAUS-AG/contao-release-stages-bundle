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

use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseCopierLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\FileServerCopierLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\VersioningLogic;
use Doctrine\DBAL\Exception;

class ReleaseStagesEventListener
{
    private VersioningLogic $_versioningLogic;
    private DatabaseCopierLogic $_databaseCopierLogic;
    private FileServerCopierLogic $_fileServerCopierLogic;

    public function __construct(VersioningLogic $versioningLogic, DatabaseCopierLogic $databaseCopierLogic,
                                FileServerCopierLogic $fileServerCopierLogic)
    {
        $this->_versioningLogic = $versioningLogic;
        $this->_databaseCopierLogic = $databaseCopierLogic;
        $this->_fileServerCopierLogic = $fileServerCopierLogic;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function onSubmitCallback() : void
    {
        $this->_versioningLogic->setNewVersionAutomatically();
        $this->_databaseCopierLogic->copy();
        // $this->_fileServerCopierLogic->copy();
    }
}
