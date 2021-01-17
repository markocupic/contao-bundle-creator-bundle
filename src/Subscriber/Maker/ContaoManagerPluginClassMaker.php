<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\Subscriber\Maker;

use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;

class ContaoManagerPluginClassMaker extends AbstractMaker
{
    /**
     * Add the Contao Manager Plugin class to file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        $source = sprintf(
            '%s/src/ContaoManager/Plugin.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/ContaoManager/Plugin.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);
    }
}
