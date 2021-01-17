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

class ContinuousIntegrationMaker extends AbstractMaker
{
    /**
     * @throws \Exception
     */
    public function addToFileStorage(): void
    {
        // Add phpunit.xml.dist
        $source = sprintf(
            '%s/phpunit.xml.tpl.dist',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/phpunit.xml.dist',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);

        // Add plugin test
        $source = sprintf(
            '%s/tests/ContaoManager/PluginTest.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/tests/ContaoManager/PluginTest.php',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);

        // Add .travis.yml
        $source = sprintf(
            '%s/.travis.tpl.yml',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/.travis.yml',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);

        // Add github workflow file
        $source = sprintf(
            '%s/.github/workflows/ci.tpl.yml',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/.github/workflows/ci.yml',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);
    }
}
