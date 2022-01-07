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

use Contao\Backend;

$GLOBALS['TL_DCA']['tl_release_stages_settings'] = array(
    'config' => array(
        'dataContainer' => 'File',
        'closed' => true
    ),
    'fields' => array(
        'databaseServer' => array(
            'inputType' => 'text',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        ),
        'databaseName' => array(
            'inputType' => 'text',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        ),
        'databasePort' => array(
            'inputType' => 'text',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        ),
        'databaseUsername' => array(
            'inputType' => 'text',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        ),
        'ignoredTables' => array(
            'inputType' => 'text',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        ),
        'databasePassword' => array(
            'inputType' => 'password',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        ),
        'fileserver' => array(
            'inputType' => 'text',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        ),
        'fileserverPort' => array(
            'inputType' => 'text',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        ),
        'fileserverUsername' => array(
            'inputType' => 'text',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        ),
        'fileserverPassword' => array(
            'inputType' => 'password',
            'eval' => array('mandatory'=>true, 'tl_class'=>'w50')
        )
    ),
    'palettes' => array(
        'default' => '{prodDatabase},databaseServer,databaseName,databasePort,databaseUsername,ignoredTables,databasePassword;{prodFileserver},fileserver,fileserverPort,fileserverUsername,fileserverPassword'
    )
);

class tl_release_stages_settings extends Backend
{

}
