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

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;

class ContaoContentElementMaker extends AbstractMaker
{
    /**
     * Add content element files to file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        if (!$this->arrInput['addContentElement']) {
            return;
        }

        // Get the content element template name
        $strContentElementTemplateName = Str::asContaoContentElementTemplateName((string) $this->arrInput['contentelementtype']);

        // Get the content element classname
        $strContentElementClassname = Str::asContaoContentElementClassName((string) $this->arrInput['contentelementtype']);

        // Add content element class to src/Controller/ContentElement
        $source = sprintf(
            '%s/src/Controller/ContentElement/ContentElementController.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Controller/ContentElement/%s.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname'],
            $strContentElementClassname
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);

        // Add content element template
        $source = sprintf(
            '%s/src/Resources/contao/templates/ce_sample_element.tpl.html5',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/templates/%s.html5',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname'],
            $strContentElementTemplateName
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);

        // Add src/Resources/contao/dca/tl_content.php
        $source = sprintf(
            '%s/src/Resources/contao/dca/tl_content.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/dca/tl_content.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);

        // Add src/Resources/contao/languages/en/modules.php to file storage
        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/languages/en/default.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );

        if (!$this->fileStorage->hasFile($target)) {
            $source = sprintf(
                '%s/src/Resources/contao/languages/en/default.tpl.php',
                $this->skeletonPath
            );

            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);
        }
    }
}
