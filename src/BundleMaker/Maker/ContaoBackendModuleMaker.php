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

class ContaoBackendModuleMaker extends AbstractMaker
{
    /**
     * @throws \Exception
     */
    public function addToFileStorage(): void
    {
        // Add dca table file
        $source = sprintf(
            '%s/src/Resources/contao/dca/tl_sample_table.tpl.php',
            $this->skeletonPath)
        ;

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/dca/%s.php',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname'),
            $this->tagStorage->get('dcatable')
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);

        // Add dca table translation file
        $source = sprintf(
            '%s/src/Resources/contao/languages/en/tl_sample_table.tpl.php',
            $this->skeletonPath
        );
        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/languages/en/%s.php',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname'),
            $this->tagStorage->get('dcatable')
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);

        // Add a sample model
        $source = sprintf(
            '%s/src/Model/Model.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Model/%s.php',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname'),
            Str::asContaoModelClassName((string) $this->tagStorage->get('dcatable'))
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);

        // Add src/Resources/contao/languages/en/modules.php to file storage
        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/languages/en/modules.php',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        if (!$this->fileStorage->hasFile($target)) {
            $source = sprintf(
                '%s/src/Resources/contao/languages/en/modules.tpl.php',
                $this->skeletonPath
            );

            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);
        }

        // Add src/Resources/contao/languages/en/default.php to file storage
        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/languages/en/default.php',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        if (!$this->fileStorage->hasFile($target)) {
            $source = sprintf(
                '%s/src/Resources/contao/languages/en/default.tpl.php',
                $this->skeletonPath
            );

            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);
        }
    }
}
