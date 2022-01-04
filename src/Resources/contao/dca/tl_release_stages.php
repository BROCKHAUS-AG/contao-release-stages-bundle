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

use BrockhausAg\ContaoReleaseStagesBundle\Logic\DatabaseLogic;
use Contao\Backend;

$GLOBALS['TL_DCA']['tl_release_stages'] = array(
    'config' => array(
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'sql' => array(
            'keys' => array(
                'id' => 'primary'
            )
        ),
        'onsubmit_callback' => array(
            array('tl_release_stages', 'changeVersionNumber')
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
            'fields' => array('version', 'title'),
            'format' => '%s - %s',
        ),
        'operations' => array(
            'show' => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_release_stages']['show'],
                'href'       => 'act=show',
                'icon'       => 'show.gif',
                'attributes' => 'style="margin-right:3px"'
            )
        )
    ),
    'fields' => array(
        'id' => array(
            'sql' => ['type' => 'integer', 'length' => '10', 'unsigned' => true, 'autoincrement' => true]
        ),
        'tstamp' => array(
            'sql' => ['type' => 'integer', 'length' => '10', 'unsigned' => true, 'default' => 0]
        ),
        'version' => array(
            'sql' => ['type' => 'string', 'length' => '255', 'default' => 'None']
        ),
        'kindOfRelease' => array(
            'inputType' => 'select',
            'options' => array('release', 'majorRelease'),
            'reference' => &$GLOBALS['TL_LANG']['tl_release_stages']['kindOfReleaseOptions'],
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
        )
    ),
    'palettes' => array(
        '__selector__' => array('addSubpalette'),
        'default' => 'kindOfRelease,title,description'
    )
);

class tl_release_stages extends Backend
{
    private DatabaseLogic $_databaseLogic;

    public function __construct()
    {
        $this->_databaseLogic = new DatabaseLogic();
    }

    public function changeVersionNumber() : void
    {
        $release_stages = $this->_databaseLogic->getLastRows(2);
        $actualId = $release_stages->id;
        $kindOfRelease = $release_stages->kindOfRelease;

        $counter = $this->_databaseLogic->countRows($release_stages);
        $oldVersion = $release_stages->version;

        $newVersion = $this->createVersion($counter, $oldVersion, $kindOfRelease);

        $this->_databaseLogic->updateVersion($actualId, $newVersion);
    }

    private function createVersion(int $counter, string $oldVersion, string $kindOfRelease) : string
    {
        if ($counter > 0) {
            $version = explode(".", $oldVersion);
            var_dump($version);
            if (strcmp($kindOfRelease, "release") == 0) {
                return $this->createRelease($version);
            }
            return $this->createMajorRelease($version);
        }
        return "1.0";
    }

    private function createRelease(array $version) : string
    {
        return $version[0]. ".". intval($version[1]+1);
    }

    private function createMajorRelease(array $version) : string
    {
        return intval($version[0]+1). ".0";
    }
}
