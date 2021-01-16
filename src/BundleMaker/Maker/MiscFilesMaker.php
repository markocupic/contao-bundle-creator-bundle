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
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class MiscFilesMaker extends AbstractMaker
{
    /**
     * @throws \Exception
     */
    public function generate(): void
    {
        // src/Resources/config/*.yml yaml config files
        $arrFiles = [
            'listener.tpl.yml',
            'parameters.tpl.yml',
            'services.tpl.yml'
        ];

        if ($this->tagStorage->get('addCustomRoute')) {
            $arrFiles[] = 'routes.tpl.yml';
        }

        foreach ($arrFiles as $file) {
            $source = sprintf(
                '%s/src/Resources/config/%s',
                $this->skeletonPath, $file
            );

            $target = sprintf(
                '%s/vendor/%s/%s/src/Resources/config/%s',
                $this->projectDir,
                $this->tagStorage->get('vendorname'),
                $this->tagStorage->get('repositoryname'),
                str_replace('tpl.', '', $file)
            );

            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);

            // Validate config files
            try {
                $arrYaml = Yaml::parse($this->fileStorage->getContent());

                if ('listener.tpl.yml' === $file || 'services.tpl.yml' === $file) {
                    if (!\array_key_exists('services', $arrYaml)) {
                        throw new ParseException('Key "services" not found. Please check the indents.');
                    }
                }

                if ('parameters.tpl.yml' === $file) {
                    if (!\array_key_exists('parameters', $arrYaml)) {
                        throw new ParseException('Key "parameters" not found. Please check the indents.');
                    }
                }
            } catch (ParseException $exception) {
                throw new ParseException(sprintf('Unable to parse the YAML string in %s: %s', $target, $exception->getMessage()));
            }
        }

        // src/Resource/contao/config/config.php
        $source = sprintf(
            '%s/src/Resources/contao/config/config.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/config/config.php',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        $this->fileStorage->addFile($source, $target);

        // Add logo
        $source = sprintf(
            '%s/src/Resources/public/logo.png',
            $this->skeletonPath
        );
        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/public/logo.png',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        $this->fileStorage->addFile($source, $target);

        // Readme.md
        $source = sprintf(
            '%s/README.tpl.md',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/README.md',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        $this->fileStorage->addFile($source, $target);

        // .gitattributes
        $source = sprintf(
            '%s/.gitattributes.tpl.txt',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/.gitattributes',
            $this->projectDir,
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
        );

        $this->fileStorage->addFile($source, $target);
    }
}

