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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\Release;

use BrockhausAg\ContaoReleaseStagesBundle\Exception\Release\ReleaseRollback;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Database\DatabaseRollbacker;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\FileSystem\FileSystemRollbacker;
use Exception;

class ReleaseRollbacker
{
    private FileSystemRollbacker $_fileSystemRollbacker;
    private DatabaseRollbacker $_databaseRollbacker;

    public function __construct(FileSystemRollbacker $fileSystemRollbacker, DatabaseRollbacker $databaseRollbacker)
    {
        $this->_fileSystemRollbacker = $fileSystemRollbacker;
        $this->_databaseRollbacker = $databaseRollbacker;
    }

    /**
     * @throws ReleaseRollback
     */
    public function rollback(): void
    {
        try {
            $this->_fileSystemRollbacker->rollback();
            $this->_databaseRollbacker->rollback();
        }catch (Exception $exception) {
            throw new ReleaseRollback("Couldn't rollback release: $exception");
        }
    }
}
