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

use BrockhausAg\ContaoReleaseStagesBundle\Constants\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Model\ReleaseStages;

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['release'] = array(
    'release_stages' => array(
        'tables' => array(Constants::DEPLOYMENT_TABLE)
    )
);

/**
 * Models
 */
$GLOBALS['TL_MODELS'][Constants::DEPLOYMENT_TABLE] = ReleaseStages::class;
