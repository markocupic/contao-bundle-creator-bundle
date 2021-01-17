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

class EasyCodingStandardMaker extends AbstractMaker
{
    /**
     * @throws \Exception
     */
    public function addFilesToStorage(): void
    {
        // .ecs/*.*
        $source = sprintf(
            '%s/.ecs',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/.ecs',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );
        // Add to storage
        $arrFiles = $this->fileStorage->addFilesFromFolder($source, $target, true);

        // Replace tags
        foreach ($arrFiles as $strTargetPath) {
            $this->fileStorage->getFile($strTargetPath)->replaceTags($this->tagStorage, ['.tpl.']);
        }
    }
}
