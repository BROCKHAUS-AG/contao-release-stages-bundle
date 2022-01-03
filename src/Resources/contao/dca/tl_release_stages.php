<?php

declare(strict_types=1);

/*
 * This file is part of contao-release-stages-bundle.
 *
 * (c) BROCKHAUS AG 2021 <info@brockhaus-ag.de>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/brockhaus-ag/contao-release-stages-bundle
 */

use Contao\Backend;
use Contao\DC_Table;
use Contao\Input;

/**
 * Table tl_release_stages
 */
$GLOBALS['TL_DCA']['tl_release_stages'] = array(

    // Config
    'config' => array(
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'sql' => array(
            'keys' => array(
                'id' => 'primary'
            )
        ),
    ),
    'edit' => array(
        'buttons_callback' => array(
            array('tl_release_stages', 'buttonsCallback')
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
            'fields' => array('version', 'title', 'kindOfRelease'),
            'format' => '%s - %s - %s',
        ),
        'operations' => array(
            // should be later a feature to go a version back
            /*'delete' => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_release_stages']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),*/
            'show' => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_release_stages']['show'],
                'href'       => 'act=show',
                'icon'       => 'show.gif',
                'attributes' => 'style="margin-right:3px"'
            )
        )
    ),
    // Palettes
    'palettes' => array(
        '__selector__' => array('addSubpalette'),
        'default' => 'kindOfRelease,title,description'
    ),
    // Fields (database)
    'fields' => array(
        'id' => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array(
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'version' => array(
            'sql' => "varchar(255) NOT NULL default '1.0'"
        ),
        'kindOfRelease' => array(
            'inputType' => 'select',
            'options' => ['release' => 'Release', 'majorRelease' => 'Major Release'],
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => 1,
            'eval' => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'title' => array(
            'inputType' => 'text',
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => 1,
            'eval' => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'description' => array(
            'inputType' => 'textarea',
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => 1,
            'eval' => array('mandatory' => true, 'maxlength' => 1024, 'tl_class' => 'clr w50'),
            'sql' => "varchar(1024) NOT NULL default ''"
        )
    )
);

/**
 * Class tl_release_stages
 */
class tl_release_stages extends Backend
{
    /**
     * @param $arrButtons
     * @param  DC_Table $dc
     * @return mixed
     */
    public function buttonsCallback($arrButtons, DC_Table $dc)
    {
        if (Input::get('act') === 'edit')
        {
            /*$arrButtons['createRelease'] = '<button type="submit" name="customButton" id="customButton" class="tl_submit customButton" accesskey="x">' . $GLOBALS['TL_LANG']['tl_release_stages']['createRelease'] . '</button>';
            $arrButtons['createMajorRelease'] = '<button type="submit" name="customButton" id="customButton" class="tl_submit customButton" accesskey="x">' . $GLOBALS['TL_LANG']['tl_release_stages']['createMajorRelease'] . '</button>';
        */}

        return $arrButtons;
    }
}
