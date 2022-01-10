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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FileServer;

use BrockhausAg\ContaoReleaseStagesBundle\Logic\IOLogic;

DEFINE("PATH", "/var/www/html/contao/files/");

class CopyToFileServerLogic {
    private LoadFromLocalLogic $_loadFromLocalLogic;

    private array $config;

    public function __construct()
    {
        $this->_loadFromLocalLogic = new LoadFromLocalLogic(PATH);
        $ioLogic = new IOLogic();
        $this->config = $ioLogic->loadFileServerConfiguration();
    }

    public function copyToFileServer()
    {
        $filesWithTimestamp = $this->_loadFromLocalLogic->loadFromLocal();
        die;
    }
}
