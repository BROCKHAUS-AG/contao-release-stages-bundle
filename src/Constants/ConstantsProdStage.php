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

abstract class ConstantsProdStage
{
    public const SCRIPT_DIRECTORY = "/scripts";

    public const CREATE_STATE_SCRIPT = self::SCRIPT_DIRECTORY. "/create_state.sh";

    public const MIGRATE_DATABASE_POLL_FILE = self::SCRIPT_DIRECTORY. "/migrate_database";

    public const BACKUP_DIRECTORY_SCRIPTS = self::SCRIPT_DIRECTORY. "/backup";
    public const BACKUP_FILE_SYSTEM_SCRIPT = self::BACKUP_DIRECTORY_SCRIPTS. "/backup_file_system.sh";
    public const BACKUP_FILE_SYSTEM_POLL_FILENAME = self::BACKUP_DIRECTORY_SCRIPTS. "/file_system_backup";
    public const BACKUP_DATABASE_SCRIPT = self::BACKUP_DIRECTORY_SCRIPTS. "/backup_prod_database.sh";
    public const BACKUP_DATABASE_POLL_FILENAME = self::BACKUP_DIRECTORY_SCRIPTS. "/database_backup";

    public const BACKUP_DIRECTORY = "/backups";
    public const BACKUP_FILE_SYSTEM_PATH = self::BACKUP_DIRECTORY. "/file_system/". Constants::FILE_TIMESTAMP_PATTERN. ".tar.gz" ;
    public const BACKUP_DATABASE_PATH = self::BACKUP_DIRECTORY. "/database/". Constants::FILE_TIMESTAMP_PATTERN. ".tar.gz";

    public const UN_ARCHIVE_SCRIPT = self::SCRIPT_DIRECTORY. "/un_archive.sh";
    public const MIGRATE_DATABASE_SCRIPT = self::SCRIPT_DIRECTORY. "/migrate_database.sh";

    public const MIGRATION_DIRECTORY = "/migrations";
    public const DATABASE_EXTRACTED_MIGRATION_DIRECTORY = self::MIGRATION_DIRECTORY. "/database";
    public const DATABASE_EXTRACTED_MIGRATION_FILE = self::DATABASE_EXTRACTED_MIGRATION_DIRECTORY. "/database_migration.sql";
    public const DATABASE_MIGRATION_FOLDER = self::MIGRATION_DIRECTORY. "/database_migration";
    public const DATABASE_MIGRATION_FILE = self::DATABASE_MIGRATION_FOLDER. "/". Constants::FILE_TIMESTAMP_PATTERN. ".tar.gz";
    public const FILE_SYSTEM_MIGRATION_FOLDER = self::MIGRATION_DIRECTORY. "/file_system_migration";
    public const FILE_SYSTEM_MIGRATION_FILE = self::FILE_SYSTEM_MIGRATION_FOLDER. "/". Constants::FILE_TIMESTAMP_PATTERN. ".tar.gz";
    public const FILE_SYSTEM_PATH = "/files/content";

    public const DATABASE_MIGRATION_FILE_COMPRESSED = "database_migration";
    public const DATABASE_ROLLBACK_FILE_NAME = "database_rollback";
    public const DATABASE_ROLLBACK_DIRECTORY = self::BACKUP_DIRECTORY. "/database_rollback";
    public const FILE_SYSTEM_MIGRATION_FILE_NAME = "file_system_migration";
    public const FILE_SYSTEM_ROLLBACK_FILE_NAME = "file_system_rollback";

}
