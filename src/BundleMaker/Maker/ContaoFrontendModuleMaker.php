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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Maker;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;

class ContaoFrontendModuleMaker extends AbstractMaker
{
    /**
     * @throws \Exception
     */
    public function addFilesToStorage(): void
    {
        // Get the frontend module template name
        $strFrontenModuleTemplateName = Str::asContaoFrontendModuleTemplateName((string) $this->arrInput['frontendmoduletype']);

        // Get the frontend module classname
        $strFrontendModuleClassname = Str::asContaoFrontendModuleClassName((string) $this->arrInput['frontendmoduletype']);

        // Add frontend module class to src/Controller/FrontendModuleController
        $source = sprintf(
            '%s/src/Controller/FrontendModule/FrontendModuleController.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Controller/FrontendModule/%s.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname'],
            $strFrontendModuleClassname
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);

        // Add frontend module template
        $source = sprintf(
            '%s/src/Resources/contao/templates/mod_sample_module.tpl.html5',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/templates/%s.html5',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname'],
            $strFrontenModuleTemplateName
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);

        // Add src/Resources/contao/dca/tl_module.php
        $source = sprintf(
            '%s/src/Resources/contao/dca/tl_module.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/dca/tl_module.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);

        // Add src/Resources/contao/languages/en/modules.php to file storage
        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/languages/en/modules.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );

        if (!$this->fileStorage->hasFile($target)) {
            $source = sprintf(
                '%s/src/Resources/contao/languages/en/modules.tpl.php',
                $this->skeletonPath
            );

            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);
        }

        // Add src/Resources/contao/languages/en/default.php to file storage
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
