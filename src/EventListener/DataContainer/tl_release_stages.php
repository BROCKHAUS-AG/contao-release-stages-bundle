<?php

declare(strict_types=1);


namespace BrockhausAg\ContaoReleaseStagesBundle\EventListener\DataContainer;

use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\CopyToDatabaseLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\CopyToFileServerLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Versioning\VersioningLogic;

class tl_release_stages
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

    public function onSubmitCallback() : void
    {
        $this->_versioningLogic->changeVersionNumber();
        $this->copy();
    }

    private function copy() : void
    {
        $this->_copyToDatabaseLogic->copyToDatabase();
        $this->_copyToFileServerLogic->copyToFileServer();
    }
}
