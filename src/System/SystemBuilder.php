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

namespace BrockhausAg\ContaoReleaseStagesBundle\System;

class SystemBuilder
{
    private SystemConfig $_systemConfig;
    private ScriptFileSynchronizer $_scriptFileSynchronizer;

    public function __construct(SystemConfig $systemConfig, ScriptFileSynchronizer $scriptFileSynchronizer)
    {
        $this->_systemConfig = $systemConfig;
        $this->_scriptFileSynchronizer = $scriptFileSynchronizer;
    }

    /**
     * This function is called from dependency injection while injecting this dependency
     */
    public function build(): void
    {
        $this->loadConfig();
        $this->synchronizeScriptFiles();
    }

    private function loadConfig(): void
    {
        $this->_systemConfig->loadConfig();
    }

    private function synchronizeScriptFiles(): void
    {
        $this->_scriptFileSynchronizer->synchronize();
    }
}
