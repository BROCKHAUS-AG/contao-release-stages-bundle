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
namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH;

class SSHRunner
{
    private $_conn;

    public function __construct($conn)
    {
        $this->_conn = $conn;
    }

    /**
     * @return false|resource
     */
    public function execute(string $command)
    {
        $stream = ssh2_exec($this->_conn, $command, );
        $dioStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        stream_set_blocking($dioStream, true);
        return stream_get_contents($dioStream);
    }

    public function executeScript(string $scriptName, array $tags = array())
    {
        return $this->execute("bash $scriptName ". implode(" ", $tags));
    }

    public function executeBackgroundScript(string $scriptName, array $tags = array()): int
    {
        $data = $this->executeScript("$scriptName ". implode(" ", $tags). " & echo $!;");
        return $this->getResourceId($data);
    }

    /**
     * Pattern would be something like this /root/migrations/*file_system_migration.tar.gz
     * The * is the placeholder to search
     */
    public function getPathOfLatestFileWithPattern(string $patternToSearch): string
    {
        $data = $this->execute("ls -t $patternToSearch | head -1");
        return strval($data);
    }

    private function getResourceId($data): int
    {
        return intval(substr($data, strpos($data, " ") + 0));
    }
}
