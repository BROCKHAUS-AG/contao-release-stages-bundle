<?php

/*
 * This file is part of contao-release-stages-bundle.
 *
 * (c) BROCKHAUS AG 2022 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-release-stages-bundle
 */
declare(strict_types=1);

namespace BrockhausAg\ContaoReleaseStagesBundle\Tests\Logic;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\Poll;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Poll\PollTimeout;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\FTPConnector;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FTP\SFTPRunner;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\Poller;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Poller\RemoteFilePoller;
use Contao\TestCase\ContaoTestCase;
use Exception;

/**
 * Class RemoteFilePollerTest
 *
 * @package BrockhausAg\ContaoReleaseStagesBundle\Logic\
 */
class RemoteFilePollerTest extends ContaoTestCase
{
    public function testInstantiation(): void
    {
        self::assertInstanceOf(Poller::class,
            new RemoteFilePoller(self::createMock(FTPConnector::class)));
    }

    public function testPollFileWhenSuccessFileExistsBreak(): void
    {
        $ftpConnectorMock = self::createMock(FTPConnector::class);
        $sftpRunnerMock = self::createMock(SFTPRunner::class);
        $sftpRunnerMock
            ->method("checkIfFileExists")
            ->with("file.success")
            ->willReturn(true);
        $ftpConnectorMock->method("connect")->willReturn($sftpRunnerMock);

        $poller = new RemoteFilePoller($ftpConnectorMock);

        try {
            $poller->pollFile("file");
        } catch (Exception $e) {
            self::fail("Exception was thrown. The method should break");
        }
        self::assertTrue(true);
    }

    /**
     * @throws PollTimeout
     */
    public function testPollFileWhenFailFileExistsThrowException(): void
    {
        $ftpConnectorMock = self::createMock(FTPConnector::class);
        $sftpRunnerMock = self::createMock(SFTPRunner::class);
        $sftpRunnerMock
            ->expects(self::at(0))
            ->method("checkIfFileExists")
            ->with("file.success")
            ->willReturn(false);
        $sftpRunnerMock
            ->expects(self::at(1))
            ->method("checkIfFileExists")
            ->with("file.fail")
            ->willReturn(true);
        $ftpConnectorMock->method("connect")->willReturn($sftpRunnerMock);

        $poller = new RemoteFilePoller($ftpConnectorMock);
        self::expectException(Poll::class);
        $poller->pollFile("file");
    }
}
