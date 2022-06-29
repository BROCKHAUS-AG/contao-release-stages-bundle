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

    public const BACKUP_DIRECTORY = self::SCRIPT_DIRECTORY. "/backup";
    public const BACKUP_FILE_SYSTEM_SCRIPT = self::BACKUP_DIRECTORY. "/backup_file_system.sh";
    public const BACKUP_FILE_SYSTEM_POLL_FILENAME = self::BACKUP_DIRECTORY. "/file_system_backup";
    public const BACKUP_DATABASE_SCRIPT = self::BACKUP_DIRECTORY. "/backup_database.sh";
    public const BACKUP_DATABASE_POLL_FILENAME = self::BACKUP_DIRECTORY. "/database_backup";
    public const UN_ARCHIVE_SCRIPT = self::SCRIPT_DIRECTORY. "/un_archive.sh";
    public const MIGRATE_DATABASE_SCRIPT = self::SCRIPT_DIRECTORY. "/migrate_database.sh";

    public const MIGRATION_DIRECTORY = "/migrations";
    public const DATABASE_EXTRACTED_MIGRATION_DIRECTORY = self::MIGRATION_DIRECTORY. "/database";
    public const DATABASE_EXTRACTED_MIGRATION_FILE = self::DATABASE_EXTRACTED_MIGRATION_DIRECTORY. "/database_migration.sql";
    public const DATABASE_MIGRATION_FOLDER = self::MIGRATION_DIRECTORY. "/database_migration";
    public const DATABASE_MIGRATION_FILE = self::DATABASE_MIGRATION_FOLDER. "/%timestamp%.tar.gz";
    public const FILE_SYSTEM_MIGRATION_FOLDER = self::MIGRATION_DIRECTORY. "/file_system_migration";
    public const FILE_SYSTEM_MIGRATION_FILE = self::FILE_SYSTEM_MIGRATION_FOLDER. "/%timestamp%.tar.gz";
}
