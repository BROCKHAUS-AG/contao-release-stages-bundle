<?php

declare(strict_types=1);


namespace BrockhausAg\ContaoReleaseStagesBundle\EventListener\DataContainer;


use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\CopyToDatabaseLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseLogic;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\CopyToFileServerLogic;
use Contao\DataContainer;
use Psr\Log\LoggerInterface;

class CreateReleaseListener
{
    private CreateReleaseListener $_copyToDatabaseListener;

    private DatabaseLogic $_databaseLogic;
    private CopyToDatabaseLogic $_copyToDatabaseLogic;
    private CopyToFileServerLogic $_copyToFileServerLogic;

    public function __construct(LoggerInterface $logger)
    {
        $this->_databaseLogic = new DatabaseLogic($logger);
        $this->_copyToDatabaseLogic = new CopyToDatabaseLogic($logger);
        $this->_copyToFileServerLogic = new CopyToFileServerLogic($logger);
    }

    /**
     * @Callback(table="tl_release_stages", target="config.onsubmit")
     */
    public function onSubmitCallback(DataContainer $dc) : void
    {
        echo "Hello from callback";
        die;

        $this->changeVersionNumber();
        $this->copy();
    }

    public function changeVersionNumber() : void
    {
        $release_stages = $this->_databaseLogic->getLastRows(2, array("id", "version", "kindOfRelease"),
            "tl_release_stages");
        $actualId = $release_stages->id;
        $kindOfRelease = $release_stages->kindOfRelease;

        $counter = $this->_databaseLogic->countRows($release_stages);
        $oldVersion = $release_stages->version;

        $newVersion = $this->createVersion($counter, $oldVersion, $kindOfRelease);

        $this->_databaseLogic->updateVersion($actualId, $newVersion);
    }

    private function createVersion(int $counter, string $oldVersion, string $kindOfRelease) : string
    {
        if ($counter > 0) {
            $version = explode(".", $oldVersion);
            if (strcmp($kindOfRelease, "release") == 0) {
                return $this->createRelease($version);
            }
            return $this->createMajorRelease($version);
        }
        return "1.0";
    }

    private function createRelease(array $version) : string
    {
        return $version[0]. ".". intval($version[1]+1);
    }

    private function createMajorRelease(array $version) : string
    {
        return intval($version[0]+1). ".0";
    }

    public function copy() : void
    {
        $this->_copyToDatabaseLogic->copyToDatabase();
        $this->_copyToFileServerLogic->copyToFileServer();
    }
}
