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
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseProd;
use Throwable;

class DeleteStatementsMigrationBuilder
{
    private Database $_database;
    private DatabaseProd $_databaseProd;

    public function __construct(Database $database, DatabaseProd $databaseProd)
    {
        $this->_database = $database;
        $this->_databaseProd = $databaseProd;
    }

    /**
     * @throws DeleteMigrationBuilder
     */
    public function build(): array
    {
        $deleteStatements = array();
        try {
            $lastId = $this->_databaseProd->getLastIdFromTlLogTable();
            $statements = $this->_database->getRowsFromTlLogTableWhereIdIsBiggerThanIdAndTextIsLikeDeleteFrom($lastId);
            foreach ($statements as $statement) {
                $deleteStatements[] = $statement["text"];
            }
        }catch (Throwable $e) {
            throw new DeleteMigrationBuilder("Couldn't build delete statements: $e");
        }

        return $deleteStatements;
    }
}
