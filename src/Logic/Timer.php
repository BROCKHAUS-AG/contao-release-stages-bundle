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

use DateTime;

class Timer {
    private DateTime $_dateTime;
    private IO $_io;
    private int $startTime;
    private int $endTIme;

    public function __construct(IO $io)
    {
        $this->_dateTime = new DateTime();
        $this->_io = $io;
    }

    public function start(): void
    {
        $this->startTime = $this->_dateTime->getTimestamp();
    }

    public function getActualTime(): int
    {
        return $this->_dateTime->getTimestamp();
    }

    public function getSpendTime(): int
    {
        return $this->getActualTime() - $this->startTime;
    }

    public function checkIfTimeIsOver(): bool
    {
        $maxSpendTime = $this->_io->getMaxSpendTimeWhileCreatingRelease();
        return false;
    }

    public function stop(): void
    {
        $this->endTIme = $this->_dateTime->getTimestamp();
    }
}
