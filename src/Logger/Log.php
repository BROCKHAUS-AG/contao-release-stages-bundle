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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logger;

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use const TL_ACCESS;

class Log {
    private LoggerInterface $_logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    public function info(string $message)
    {
        $this->log(LogLevel::INFO, $message);
    }

    public function warning(string $message)
    {
        $this->log(LogLevel::WARNING, $message);
    }

    public function error(string $message)
    {
        $this->log(LogLevel::ERROR, $message);
    }

    public function logErrorAndDie(string $message): void
    {
        $this->error($message);
        die($message);
    }

    private function log(string $level, string $message): void
    {
        $this->_logger->log(
            $level,
            $message,
            ['contao' => new ContaoContext(__METHOD__, TL_ACCESS)]);
    }
}
