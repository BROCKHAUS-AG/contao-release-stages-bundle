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
    private IO $_io;
    private int $startTime;

    public function __construct(IO $io)
    {
        $this->_io = $io;
    }

    private function createDateTime(): DateTime
    {
        return new DateTime();
    }

    public function start(): void
    {
        $this->startTime = $this->createDateTime()->getTimestamp();
    }

    public function getActualTime(): int
    {
        return $this->createDateTime()->getTimestamp();
    }

    public function getSpendTime(): int
    {
        return $this->getActualTime() - $this->startTime;
    }

    public function getMaxTime(): int
    {
        return $this->_io->getMaxSpendTimeWhileCreatingRelease();
    }

    public function checkIfTimeIsOver(): bool
    {
        $maxSpendTime = $this->getMaxTime() * 60;
        return $this->getSpendTime() <= $maxSpendTime;
    }
}
