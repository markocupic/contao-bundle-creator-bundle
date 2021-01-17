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

class DependencyInjectionExtensionClassMaker extends AbstractMaker
{
    public function addFilesToStorage(): void
    {
        $source = sprintf(
            '%s/src/DependencyInjection/Extension.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/DependencyInjection/%s.php',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname'),
            Str::asDependencyInjectionExtensionClassName((string) $this->tagStorage->get('vendorname'), (string) $this->tagStorage->get('repositoryname'))
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);
    }
}
