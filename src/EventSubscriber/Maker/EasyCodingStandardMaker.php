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

use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;

final class EasyCodingStandardMaker extends AbstractMaker
{
    public const PRIORITY = 940;

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
     * Add easy coding standard config files to the bundle.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        if (!$this->input->addEasyCodingStandard) {
            return;
        }

        // tools/ecs/*.*
        $source = sprintf(
            '%s/tools/ecs',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/tools/ecs',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        // Add to storage
        $arrFiles = $this->fileStorage->addFilesFromFolder($source, $target, true);

        // Replace tags
        foreach ($arrFiles as $strTargetPath) {
            $this->fileStorage->getFile($strTargetPath);
        }
    }
}
