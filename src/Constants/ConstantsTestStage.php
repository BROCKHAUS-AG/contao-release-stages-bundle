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

namespace BrockhausAg\ContaoReleaseStagesBundle\Constants;

abstract class ConstantsTestStage
{
    public const SCRIPT_DIRECTORY = Constants::PATH_TO_VENDOR. "/scripts";

    public const CREATE_STATE_SCRIPT = self::SCRIPT_DIRECTORY. "/create_state.sh";

    public const REMOTE_DIRECTORY = self::SCRIPT_DIRECTORY. "/remote";

    public const BACKUP_DIRECTORY = self::REMOTE_DIRECTORY. "/backup";
    public const BACKUP_FILE_SYSTEM_SCRIPT = self::BACKUP_DIRECTORY. "/backup_file_system.sh";
    public const BACKUP_DATABASE_SCRIPT = self::BACKUP_DIRECTORY. "/backup_database.sh";

    public const UN_ARCHIVE_SCRIPT = self::REMOTE_DIRECTORY. "/un_archive.sh";
    public const MIGRATE_DATABASE_SCRIPT = self::REMOTE_DIRECTORY. "/migrate_database.sh";

    public const LOCAL_DIRECTORY = self::SCRIPT_DIRECTORY. "/local";
    public const CREATE_ARCHIVE_SCRIPT = self::LOCAL_DIRECTORY. "/create_archive.sh";

    public const MIGRATION_DIRECTORY = "/migrations";

    public const DATABASE_MIGRATION_DIRECTORY = self::MIGRATION_DIRECTORY. "/database";
    public const DATABASE_MIGRATION_FILE = self::DATABASE_MIGRATION_DIRECTORY. "/database_migration.sql";
    public const DATABASE_MIGRATION_FILE_COMPRESSED = "database_migration";
    public const FILE_SYSTEM_MIGRATION_FILE_NAME = "file_system_migration";
}
