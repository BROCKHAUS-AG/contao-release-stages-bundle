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

use BrockhausAg\ContaoReleaseStagesBundle\Exception\FileSystem\FileSystemRollback;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\SSH\SSHConnection;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\SSH\SSHRunner;
use Exception;

class Rollbacker
{
    private SSHConnector $_sshConnection;
    private Extractor $_extractor;

    public function __construct(SSHConnector $sshConnection, Extractor $extractor)
    {
        $this->_sshConnection = $sshConnection;
        $this->_extractor = $extractor;
    }

    /**
     * @throws SSHConnection
     * @throws FileSystemRollback
     */
    public function rollback(string $extractTo, string $pollFileName, string $path,
                             string $patternWithPath, string $pattern): void
    {
        $runner = $this->_sshConnection->connect();
        try {
            $toBeExtracted = $this->getNameOfLatestBackup($runner, $patternWithPath, $pattern);
            $this->_extractor->extract($runner, $toBeExtracted, $extractTo, $pollFileName, $path);
        } catch (Exception $e) {
            throw new FileSystemRollback("Couldn't rollback: $e");
        }finally {
            $this->_sshConnection->disconnect();
        }
    }

    private function getNameOfLatestBackup(SSHRunner $runner, string $path, string $pattern): string
    {
        return $runner->getPathOfLatestFileWithPattern(str_replace($pattern, "*", $path));
    }
}
