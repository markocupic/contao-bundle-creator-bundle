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

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;

final class CustomRouteMaker extends AbstractMaker
{
    public const PRIORITY = 900;

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

        if (!$this->input->addCustomRoute) {
            return;
        }

        /** @var Str $strAdapter */
        $strAdapter = $this->framework->getAdapter(Str::class);

        $subject = sprintf(
            '%s_%s',
            strtolower($this->input->vendorname),
            strtolower($this->input->repositoryname)
        );
        $subject = preg_replace('/-bundle$/', '', $subject);
        $routeId = preg_replace('/-/', '_', $subject);
        $this->tagStorage->set('routeid', $routeId);
        $this->tagStorage->set('twignamespace', $strAdapter->asTwigNameSpace((string) $this->input->vendorname, (string) $this->input->repositoryname));
    }

    /**
     * Add a custom route to the file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        if (!$this->input->addCustomRoute) {
            return;
        }

        // Add controller (custom route)
        $source = sprintf(
            '%s/src/Controller/Controller.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Controller/MyCustomController.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add twig template
        $source = sprintf(
            '%s/src/Resources/views/MyCustom/my_custom.html.tpl.twig',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/views/MyCustom/my_custom.html.twig',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }
    }
}
