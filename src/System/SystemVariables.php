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

abstract class SystemVariables
{
    public const PATH_TO_VENDOR = "vendor/brockhaus-ag/contao-release-stages/";


    public const SETTINGS_DIRECTORY = "/settings/brockhaus-ag/contao-release-stages-bundle";
    public const CONFIG_FILE = self::SETTINGS_DIRECTORY. "/config.json";


    public const SCRIPT_DIRECTORY = self::PATH_TO_VENDOR. "scripts";

    public const BACKUP_DIRECTORY = self::SCRIPT_DIRECTORY. "/backup";
    public const BACKUP_FILE_SYSTEM_SCRIPT = self::BACKUP_DIRECTORY. "/backup_file_system.sh";
    public const BACKUP_DATABASE_SCRIPT = self::BACKUP_DIRECTORY. "/backup_database.sh";


    public const SCRIPT_DIRECTORY_PROD = "/scripts";

    public const BACKUP_DIRECTORY_PROD = self::SCRIPT_DIRECTORY_PROD. "/backup";
    public const BACKUP_FILE_SYSTEM_SCRIPT_PROD = self::BACKUP_DIRECTORY_PROD. "/backup_file_system.sh";
    public const BACKUP_DATABASE_SCRIPT_PROD = self::BACKUP_DIRECTORY_PROD. "/backup_database.sh";


}