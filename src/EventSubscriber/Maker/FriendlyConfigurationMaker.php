<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\EventSubscriber\Maker;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;

final class FriendlyConfigurationMaker extends AbstractMaker
{
    public const PRIORITY = 890;

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

        if (!$this->input->addFriendlyConfiguration) {
            return;
        }

        /** @var Str $strAdapter */
        $strAdapter = $this->framework->getAdapter(Str::class);

        $this->tagStorage->set('dependencyinjectionextensionclassname', $strAdapter->asDependencyInjectionExtensionClassName((string) $this->input->vendorname, (string) $this->input->repositoryname));

        $strRootKey = str_replace('Bundle', '', $this->tagStorage->get('toplevelnamespace').$this->tagStorage->get('sublevelnamespace'));
        $this->tagStorage->set('friendlyconfigurationrootkey', $strAdapter->asSnakeCase($strRootKey));
    }

    /**
     * Add friendly configuration to file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        if (!$this->input->addFriendlyConfiguration) {
            return;
        }

        /** @var Str $strAdapter */
        $strAdapter = $this->framework->getAdapter(Str::class);

        $source = sprintf(
            '%s/src/DependencyInjection/Extension.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/DependencyInjection/%s.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            $strAdapter->asDependencyInjectionExtensionClassName((string) $this->input->vendorname, (string) $this->input->repositoryname)
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        $source = sprintf(
            '%s/src/DependencyInjection/Configuration.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/DependencyInjection/Configuration.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        $source = sprintf(
            '%s/src/Class.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/%s%s.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            $strAdapter->asClassName((string) $this->input->vendorname),
            $strAdapter->asClassName((string) $this->input->repositoryname)
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }
    }
}
