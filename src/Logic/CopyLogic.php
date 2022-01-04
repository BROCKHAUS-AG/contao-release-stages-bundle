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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic;

use Contao\Backend;
use mysqli;

DEFINE("PROD_SERVER", "192.168.0.2");
DEFINE("PROD_DATABASE", "prodContao");
DEFINE("PROD_USER", "prodContao");
DEFINE("PROD_USER_PASSWORD", "admin1234");

DEFINE("DO_NOT_COPY_TABLES", array("tl_release_stages", "tl_contao_bundle_creator"));

class CopyLogic extends Backend
{

    public function copyToDatabase() : void
    {
        $tables = $this->downloadFromDatabase();

        $conn = $this->createConnectionToProdDatabase();

        // insert



        /*if ($conn->query($sql) === TRUE) {
            echo "erstellt";
        }else {
            die("fehlgeschlagen");
        }
*/
        die;
        $conn->close();
    }

    public function downloadFromDatabase() : array
    {
        $tableNames = $this->getTableNamesFromDatabase();
        $table = array();
        foreach ($tableNames as $tableName)
        {
            $tableContent = $this->Database->prepare("SELECT * FROM contao.". $tableName)
                ->execute()
                ->fetchAllAssoc();
            array_push($table, array($tableName, $tableContent));
        }
        return $table;
    }

    private function getTableNamesFromDatabase() : array
    {
        $tables = $this->Database->prepare("SHOW TABLES FROM contao")
            ->execute();
        $tableNames = array();
        while ($tables->next()) {
            $tableName = $tables->Tables_in_contao;
            if (!in_array($tableName, DO_NOT_COPY_TABLES)) {
                array_push($tableNames, $tableName);
            }
        }
        return $tableNames;
    }

    private function createConnectionToProdDatabase()
    {
        $conn = new mysqli(PROD_SERVER, PROD_USER, PROD_USER_PASSWORD, PROD_DATABASE);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }else {
            echo "Connected successfully";
        }
        return $conn;
    }
}
