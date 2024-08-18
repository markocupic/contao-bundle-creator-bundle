<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\EventSubscriber\Maker;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;

final class SessionAttributeBagMaker extends AbstractMaker
{
    public const PRIORITY = 1010;

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

        if (!$this->input->addSessionAttribute) {
            return;
        }

        /** @var Str $strAdapter */
        $strAdapter = $this->framework->getAdapter(Str::class);

        $this->tagStorage->set('servicevendornamekey', $strAdapter->asSnakeCase(strtolower((string) $this->input->vendorname)));
        $this->tagStorage->set('servicerepositorynamekey', $strAdapter->asSnakeCase(strtolower((string) $this->input->repositoryname)));

        $strRootKey = str_replace('Bundle', '', $this->tagStorage->get('toplevelnamespace').$this->tagStorage->get('sublevelnamespace'));
        $this->tagStorage->set('friendlyconfigurationrootkey', $strAdapter->asSnakeCase($strRootKey));

        $this->tagStorage->set('sessionAttributeName', $strAdapter->asSessionAttributeName(sprintf('%s_%s', $this->input->vendorname, str_replace('bundle', '', $this->input->repositoryname))));
        $this->tagStorage->set('sessionAttributeKey', '_'.$strAdapter->asSessionAttributeName(sprintf('%s_%s_attributes', $this->input->vendorname, str_replace('bundle', '', $this->input->repositoryname))));
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

        if (!$this->input->addSessionAttribute) {
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
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add SessionFactory
        $source = sprintf(
            '%s/src/Session/SessionFactory.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Session/SessionFactory.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add config/services.yaml
        $source = sprintf(
            '%s/config/services.tpl.yaml',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/config/services.yaml',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }
    }
}
