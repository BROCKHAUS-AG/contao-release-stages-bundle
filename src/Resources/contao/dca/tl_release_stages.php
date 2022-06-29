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

use BrockhausAg\ContaoReleaseStagesBundle\EventListener\DataContainer\ReleaseStages;
use BrockhausAg\ContaoReleaseStagesBundle\Constants;

$GLOBALS['TL_DCA'][Constants::DEPLOYMENT_TABLE] = array(
    'config' => array(
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'sql' => array(
            'keys' => array(
                'id' => 'primary'
            )
        ),
        'onsubmit_callback' => array(
            array(ReleaseStages::class, 'onSubmitCallback'),
        )
    ),
    'list' => array(
        'sorting' => array
        (
            'mode' => 1,
            'fields' => array('version'),
            'flag' => 1,
            'panelLayout' => 'sort,search,limit'
        ),
        'label' => array(
            'fields' => array('version', 'state', 'title'),
            'format' => 'v%s - %s - %s'
        ),
        'operations' => array(
            'show' => array(
                'label'      => &$GLOBALS['TL_LANG'][Constants::DEPLOYMENT_TABLE]['show'],
                'href'       => 'act=show',
                'icon'       => 'show.gif',
                'attributes' => 'style="margin-right:3px"'
            )
        )
    ),
    'fields' => array(
        'id' => array(
            'search' => true,
            'sql' => ['type' => 'integer', 'length' => '10', 'unsigned' => true, 'autoincrement' => true]
        ),
        'tstamp' => array(
            'search' => true,
            'sql' => ['type' => 'integer', 'length' => '10', 'unsigned' => true, 'default' => 0]
        ),
        'version' => array(
            'search' => true,
            'sql' => ['type' => 'string', 'length' => '255', 'default' => 'None']
        ),
        'kindOfRelease' => array(
            'inputType' => 'select',
            'options' => array('release', 'majorRelease'),
            'reference' => &$GLOBALS['TL_LANG'][Constants::DEPLOYMENT_TABLE]['kindOfReleaseOptions'],
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => 1,
            'eval' => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql' => ['type' => 'string', 'length' => '255', 'default' => '']
        ),
        'title' => array(
            'inputType' => 'text',
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => 1,
            'eval' => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql' => ['type' => 'string', 'length' => '255', 'default' => '']
        ),
        'description' => array(
            'inputType' => 'textarea',
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => 1,
            'eval' => array('mandatory' => true, 'maxlength' => 1024, 'tl_class' => 'clr w50'),
            'sql' => ['type' => 'string', 'length' => '1024', 'default' => '']
        ),
        'state' => array(
            'search' => true,
            'sql' => ['type' => 'string', 'length' => '14', 'default' => Constants::DEPLOYMENT_PENDING]
        ),
        'information' => array(
            'sql' => ['type' => 'text', 'notnull' => false]
        ),
        'execution_time' => array(
            'sql' => ['type' => 'integer', 'notnull' => false]
        )
    ),
    'palettes' => array(
        '__selector__' => array('addSubpalette'),
        'default' => 'kindOfRelease,title,description'
    )
);
