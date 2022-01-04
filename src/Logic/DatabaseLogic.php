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

class DatabaseLogic extends Backend
{
    public function getLastRows(int $count, array $columns, string $tableName): \Contao\Database\Result
    {
        return $this->Database
            ->prepare("SELECT ". implode(", ", $columns). " FROM ". $tableName.
                " ORDER BY id DESC LIMIT ". $count)
            ->execute();
    }

    public function countRows($toCount) : int
    {
        $counter = 0;
        while ($toCount->next()) {
            $counter++;
        }
        return $counter;
    }

    public function updateVersion(int $id, string $version) : void
    {
        $this->Database
            ->prepare("UPDATE tl_release_stages %s WHERE id=". $id)
            ->set(array("version" => $version))
            ->execute(1);
    }
}
