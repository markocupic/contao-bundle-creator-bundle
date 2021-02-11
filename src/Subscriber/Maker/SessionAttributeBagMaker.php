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

class SessionAttributeBagMaker extends AbstractMaker
{
    /**
     * Add a custom route to the file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        if (!$this->arrInput['addSessionAttribute']) {
            return;
        }

        // Add attribute bag
        $source = sprintf(
            '%s/src/Session/Attribute/ArrayAttributeBag.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Session/Attribute/ArrayAttributeBag.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);
        }

        // Add AddSessionBagsPass
        $source = sprintf(
            '%s/src/DependencyInjection/Compiler/AddSessionBagsPass.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/DependencyInjection/Compiler/AddSessionBagsPass.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);
        }

        // Add BundleClass
        $source = sprintf(
            '%s/src/Class.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/%s%s.php',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname'],
            Str::asClassName((string) $this->arrInput['vendorname']),
            Str::asClassName((string) $this->arrInput['repositoryname'])
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);
        }

        // Add config/services.yml
        $source = sprintf(
            '%s/src/Resources/config/services.tpl.yml',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/config/services.yml',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);
        }
    }
}
