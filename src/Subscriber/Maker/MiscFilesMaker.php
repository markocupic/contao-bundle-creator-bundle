<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\Subscriber\Maker;

use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;

final class MiscFilesMaker extends AbstractMaker
{
    public const PRIORITY = 950;

    public static function getSubscribedEvents(): array
    {
        return [
            AddTagsEvent::NAME => ['addTagsToStorage', self::PRIORITY],
            AddMakerEvent::NAME => ['addFilesToStorage', self::PRIORITY],
        ];
    }

    public function addTagsToStorage(AddTagsEvent $event): void
    {
        parent::addTagsToStorage($event);
    }

    /**
     * Add config files, assets, etc.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        // src/Resources/config/*.yml yaml config files
        $arrFiles = [
            'listener.tpl.yml',
            'parameters.tpl.yml',
            'services.tpl.yml',
        ];

        if ($this->input->addCustomRoute) {
            $arrFiles[] = 'routes.tpl.yml';
        }

        foreach ($arrFiles as $file) {
            $source = sprintf(
                '%s/src/Resources/config/%s',
                $this->skeletonPath,
                $file
            );

            $target = sprintf(
                '%s/vendor/%s/%s/src/Resources/config/%s',
                $this->projectDir,
                $this->input->vendorname,
                $this->input->repositoryname,
                str_replace('tpl.', '', $file)
            );

            if (!$this->fileStorage->hasFile($target)) {
                $this->fileStorage->addFile($source, $target);
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
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add logo to the docs folder
        $source = sprintf(
            '%s/docs/logo.png',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/docs/logo.png',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add empty stylesheet
        $source = sprintf(
            '%s/src/Resources/public/css/styles.css',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/public/css/styles.css',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add empty script file
        $source = sprintf(
            '%s/src/Resources/public/js/script.js',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/public/js/script.js',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Readme.md
        $source = sprintf(
            '%s/README.tpl.md',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/README.md',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // .editorconfig
        $source = sprintf(
            '%s/.editorconfig.tpl.txt',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/.editorconfig',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // .gitattributes
        $source = sprintf(
            '%s/.gitattributes.tpl.txt',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/.gitattributes',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }
    }
}
