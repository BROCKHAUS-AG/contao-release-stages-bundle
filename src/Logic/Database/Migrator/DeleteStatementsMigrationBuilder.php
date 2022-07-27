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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Migrator;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Database\Migrator\DeleteMigrationBuilder;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\Database;
use Throwable;

class DeleteStatementsMigrationBuilder
{
    private Database $_database;

    public function __construct(Database $database)
    {
        $this->_database = $database;
    }

    /**
     * @throws DeleteMigrationBuilder
     */
    public function build(): array
    {
        $deleteStatements = array();
        try {
            $statements = $this->_database->getDeleteStatementsFromTlLogTable();
            foreach ($statements as $statement) {
                $deleteStatements[] = $statement["text"]. ";\n";
            }
        } catch (Throwable $e) {
            throw new DeleteMigrationBuilder("Couldn't build delete statements: $e");
        }
        return $deleteStatements;
    }
}
