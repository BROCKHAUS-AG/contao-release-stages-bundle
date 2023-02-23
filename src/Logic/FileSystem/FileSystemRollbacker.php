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

namespace BrockhausAg\ContaoReleaseStagesBundle\Logic\FileSystem;

use BrockhausAg\ContaoReleaseStagesBundle\Constants\Constants;
use BrockhausAg\ContaoReleaseStagesBundle\Constants\ConstantsProdStage;
use BrockhausAg\ContaoReleaseStagesBundle\Exception\FileSystem\FileSystemRollback;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Config;
use BrockhausAg\ContaoReleaseStagesBundle\Logic\Rollbacker;
use Exception;

class FileSystemRollbacker
{
    private Rollbacker $_rollbacker;
    private Config $_config;

    public function __construct(Rollbacker $rollbacker, Config $config)
    {
        $this->_rollbacker = $rollbacker;
        $this->_config = $config;
    }

    /**
     * @throws FileSystemRollback
     */
    public function rollback(): void
    {
        try {
            $path = $this->_config->getFileServerConfiguration()->getRootPath();
            $extractTo = $path. ConstantsProdStage::FILE_SYSTEM_PATH;
            $this->_rollbacker->rollback($extractTo,
                ConstantsProdStage::FILE_SYSTEM_ROLLBACK_FILE_NAME,
                $path, $path. ConstantsProdStage::BACKUP_FILE_SYSTEM_PATH,
                Constants::FILE_TIMESTAMP_PATTERN);
        } catch (Exception $e) {
            throw new FileSystemRollback("Couldn't rollback file system: $e");
        }
    }
}
