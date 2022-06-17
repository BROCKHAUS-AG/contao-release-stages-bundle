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


use BrockhausAg\ContaoReleaseStagesBundle\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\StateSynchronizer;
use Doctrine\DBAL\Exception;

class Finisher {
    private StateSynchronizer $_stateSynchronizer;

    public function __construct(StateSynchronizer $stateSynchronizer)
    {
        $this->_stateSynchronizer = $stateSynchronizer;
    }

    /**
     * @throws Exception
     */
    public function finishWithSuccess(int $actualId): void
    {
        $this->_stateSynchronizer->updateState(Constants::STATE_SUCCESS, $actualId);
    }

    /**
     * @throws Exception
     */
    public function finishWithOldStateIsPending(int $actualId): void
    {
        $this->_stateSynchronizer->updateState(Constants::STATE_OLD_PENDING, $actualId);
    }

    /**
     * @throws Exception
     */
    public function finishWithFailure(int $actualId): void
    {
        $this->_stateSynchronizer->updateState(Constants::STATE_FAILURE, $actualId);
    }
}
