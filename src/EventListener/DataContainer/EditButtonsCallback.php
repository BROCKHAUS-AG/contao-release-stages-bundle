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

namespace BrockhausAg\ContaoReleaseStagesBundle\EventListener\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

/**
 * @Callback(table="tl_deployments", target="edit.buttons")
 */
class EditButtonsCallback
{
    /**
     * This method is to remove "save", "save and create" and "save and duplicate" button from dca
     */
    public function __invoke(array $buttons, DataContainer $dc): array
    {
        unset($buttons['save']);
        unset($buttons['saveNcreate']);
        unset($buttons['saveNduplicate']);

        return $buttons;
    }
}
