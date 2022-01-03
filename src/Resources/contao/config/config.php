<?php

/*
 * This file is part of contao-release-stages-bundle.
 * 
 * (c) BROCKHAUS AG 2021 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-release-stages-bundle
 */

use BrockhausAg\ContaoReleaseStagesBundle\Model\ReleaseStagesModel;

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['release']['release'] = array(
    'tables' => array('tl_release_stages')
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_release_stages'] = ReleaseStagesModel::class;
