<?php

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FileSystem;

use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FileSystem\FileSystemDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\RemoteFilePoller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;
use Exception;

class FileSystemDeployer
{
    private SSHConnector $_sshConnection;
    private Poller $_poller;
    private Config $_config;

    public function __construct(SSHConnector $sshConnection, RemoteFilePoller $poller,  Config $config)
    {
        $this->_sshConnection = $sshConnection;
        $this->_poller = $poller;
        $this->_config = $config;
    }

    /**
     * @throws SSHConnection
     * @throws FileSystemDeployment
     */
    public function deploy(): void
    {
        $runner = $this->_sshConnection->connect();
        try {
            $path = $this->_config->getFileServerConfiguration()->getPath();
            $name = Constants::FILE_SYSTEM_MIGRATION_FILE_NAME;
            $file = $this->getFilePath($runner, $path);
            $this->extractFileSystem($file, $path, $runner, $name);
            $this->_poller->pollFile("$path". Constants::SCRIPT_DIRECTORY_PROD. "/un_archive_$name");
        } catch (Exception $e) {
            throw new FileSystemDeployment("Couldn't deploy file system: $e");
        }finally {
            $this->_sshConnection->disconnect();
        }
    }

    private function getFilePath(SSHRunner $runner, string $path): string
    {
        return $runner->getPathOfLatestFileWithPattern($path. str_replace("%timestamp%_", "*",
                Constants::FILE_SYSTEM_MIGRATION_FILE_PROD));
    }

    private function extractFileSystem(string $file, string $path, SSHRunner $runner, string $name): void
    {
        $tags = $this->createTags($file, $path, $name);
        $scriptPath = "$path". Constants::UN_ARCHIVE_SCRIPT_PROD;
        $runner->executeBackgroundScript($scriptPath, $tags);
    }

    private function createTags(string $file, string $path, string $name): array
    {
        return array(
            "-f \"$file\"",
            "-e \"$path/files/content\"",
            "-n \"$name\""
        );
    }
}
