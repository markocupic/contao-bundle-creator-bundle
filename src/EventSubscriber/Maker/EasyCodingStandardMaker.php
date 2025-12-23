<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\EventSubscriber\Maker;

use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;
use Markocupic\ContaoBundleCreatorBundle\EventSubscriber\Maker\Trait\ComposerJsonTrait;

final class EasyCodingStandardMaker extends AbstractMaker
{
    use ComposerJsonTrait;

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
        $source = \sprintf(
            '%s/tools/ecs',
            $this->skeletonPath,
        );

        $target = \sprintf(
            '%s/vendor/%s/%s/tools/ecs',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        // Add to storage
        $this->fileStorage->addFilesFromFolder($source, $target, true);

        // Adopt composer.json:

        // Add composer.json to file storage if not exists and set the FileStorage cursor to the new file
        $this->addComposerJsonFileToFileStorage($this->fileStorage, $this->skeletonPath, $this->projectDir, $this->input->vendorname, $this->input->repositoryname);

        $content = $this->fileStorage->getContent();

        $objComposer = json_decode($content);

        // Add config.allow-plugins.dealerdirect/phpcodesniffer-composer-installer to composer.json
        $objComposer->config->{'allow-plugins'}->{'dealerdirect/phpcodesniffer-composer-installer'} = true;

        // Add scripts.cs-fixer to composer.json
        $objComposer->scripts->{'cs-fixer'} = '@php tools/ecs/vendor/bin/ecs check config/ contao/ src/ templates/ tests/ --config tools/ecs/config/default.php --fix --ansi';

        // Encode and save composer.json
        $content = json_encode($objComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->fileStorage->replaceContent($content);
    }
}
