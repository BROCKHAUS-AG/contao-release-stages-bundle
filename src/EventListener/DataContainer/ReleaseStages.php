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

namespace BrockhausAg\ContaoReleaseStagesBundle\EventListener\DataContainer;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Release\ReleaseBuild;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Release\ReleaseDeployment;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\Release\ReleaseRollback;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Finisher;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Release\ReleaseBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Release\ReleaseDeployer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Release\ReleaseRollbacker;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Synchronizer\StateSynchronizer;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Timer;
use Exception;

class ReleaseStages
{
    private Timer $_timer;
    private StateSynchronizer $_stateSynchronizer;
    private ReleaseBuilder $_releaseBuilder;
    private ReleaseDeployer $_releaseDeployer;
    private ReleaseRollbacker $_releaseRollbacker;
    private Finisher $_finisher;

    public function __construct(Timer $timer, StateSynchronizer $stateSynchronizer, ReleaseBuilder $releaseBuilder,
                                ReleaseDeployer $releaseDeployer, ReleaseRollbacker $releaseRollbacker,
                                Finisher $finisher)
    {
        $this->_timer = $timer;
        $this->_stateSynchronizer = $stateSynchronizer;
        $this->_releaseBuilder = $releaseBuilder;
        $this->_releaseDeployer = $releaseDeployer;
        $this->_releaseRollbacker = $releaseRollbacker;
        $this->_finisher = $finisher;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     *
     * This method is called when clicking the submit button in the Release Stages DCA
     * After clicking the button, the Bundle would create a new Release
     */
    public function onSubmitCallback(): void
    {
        $this->_timer->start();
        $actualId = $this->_stateSynchronizer->getActualId();
        try {
            $this->_releaseBuilder->build($actualId);
            $this->_releaseDeployer->deploy();
            throw new ReleaseDeployment('test');
            $this->_finisher->finishWithSuccess($actualId, $this->_timer->getSpendTime());
        }catch (ReleaseBuild $exception) {
            $this->_finisher->finishWithFailure($actualId, $this->_timer->getSpendTime(), $exception->getMessage());
        }catch (ReleaseDeployment $releaseDeploymentException) {
            $this->rollback($actualId, $releaseDeploymentException);
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function rollback(int $actualId, ReleaseDeployment $releaseDeploymentException): void
    {
        try {
            $this->_releaseRollbacker->rollback();
            $this->_finisher->finishWithFailure($actualId, $this->_timer->getSpendTime(),
                $releaseDeploymentException->getMessage(), true);
        }catch (ReleaseRollback $releaseRollbackException) {
            $this->_finisher->finishWithFailure($actualId, $this->_timer->getSpendTime(),
                $releaseDeploymentException->getMessage(). $releaseRollbackException->getMessage());
        }
    }
}
