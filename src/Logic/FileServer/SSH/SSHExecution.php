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
namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer\SSH;

class SSHExecution
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
        $stream = ssh2_exec($this->_conn, $command);
        stream_set_blocking($stream, true);
        return $stream;
    }

    public function executeScript(string $scriptName)
    {
        return $this->execute("bash -r ". $scriptName);
    }

    /**
     * @return false|string
     */
    public function getResponse($stream)
    {
        $streamOut = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        return stream_get_contents($streamOut);
    }
}
