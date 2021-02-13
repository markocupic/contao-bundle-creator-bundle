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
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionAttributeBagMaker extends AbstractMaker implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AddTagsEvent::NAME => ['addTagsToStorage', 1010],
            AddMakerEvent::NAME => ['addFilesToStorage', 1010],
        ];
    }

    public function addTagsToStorage(AddTagsEvent $event): void
    {
        parent::addTagsToStorage($event);

        if (!$this->input->addSessionAttribute) {
            return;
        }

        // Set tags
        $this->tagStorage->set('sessionAttributeName', Str::asSessionAttributeName(sprintf('%s_%s', $this->input->vendorname, str_replace('bundle', '', $this->input->repositoryname))));
        $this->tagStorage->set('sessionAttributeKey', '_'.Str::asSessionAttributeName(sprintf('%s_%s_attributes', $this->input->vendorname, str_replace('bundle', '', $this->input->repositoryname))));
        $this->tagStorage->set('addSessionAttribute', (string) $this->input->addSessionAttribute);
    }

    /**
     * Add a custom route to the file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        // Add attribute bag
        $source = sprintf(
            '%s/src/Session/Attribute/ArrayAttributeBag.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Session/Attribute/ArrayAttributeBag.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add AddSessionBagsPass
        $source = sprintf(
            '%s/src/DependencyInjection/Compiler/AddSessionBagsPass.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/DependencyInjection/Compiler/AddSessionBagsPass.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add BundleClass
        $source = sprintf(
            '%s/src/Class.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/%s%s.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            Str::asClassName((string) $this->input->vendorname),
            Str::asClassName((string) $this->input->repositoryname)
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add config/services.yml
        $source = sprintf(
            '%s/src/Resources/config/services.tpl.yml',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/config/services.yml',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }
    }
}
