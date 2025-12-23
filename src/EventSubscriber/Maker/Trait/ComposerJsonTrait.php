<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\EventSubscriber\Maker\Trait;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;

trait ComposerJsonTrait
{
    public function addComposerJsonFileToFileStorage(FileStorage $fileStorage, string $skeletonPath, string $projectDir, string $vendorname, string $repositoryname): void
    {
        $source = \sprintf(
            '%s/composer.json',
            $skeletonPath,
        );

        $target = \sprintf(
            '%s/vendor/%s/%s/composer.json',
            $projectDir,
            $vendorname,
            $repositoryname,
        );

        if (!$fileStorage->has($target)) {
            $fileStorage->addFile($source, $target);
        } else {
            $fileStorage->getFile($target);
        }
    }
}
