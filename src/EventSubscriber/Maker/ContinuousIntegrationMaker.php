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

final class ContinuousIntegrationMaker extends AbstractMaker
{
    use ComposerJsonTrait;

    public const PRIORITY = 960;

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
     * Add unit tests to the file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        // tools/phpunit/*.*
        $source = \sprintf(
            '%s/tools/phpunit',
            $this->skeletonPath,
        );

        $target = \sprintf(
            '%s/vendor/%s/%s/tools/phpunit',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        // Add to storage
        $this->fileStorage->addFilesFromFolder($source, $target, true);

        // Add plugin test
        $source = \sprintf(
            '%s/tests/ContaoManager/PluginTest.php.ttpl',
            $this->skeletonPath,
        );

        $target = \sprintf(
            '%s/vendor/%s/%s/tests/ContaoManager/PluginTest.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        if (!$this->fileStorage->has($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add github workflow/ci.yml file
        $source = \sprintf(
            '%s/.github/workflows/ci.yml',
            $this->skeletonPath,
        );

        $target = \sprintf(
            '%s/vendor/%s/%s/.github/workflows/ci.yml',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        if (!$this->fileStorage->has($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Adopt composer.json:

        // Add composer.json to file storage if not exists and set the FileStorage cursor to the new file
        $this->addComposerJsonFileToFileStorage($this->fileStorage, $this->skeletonPath, $this->projectDir, $this->input->vendorname, $this->input->repositoryname);

        $content = $this->fileStorage->getContent();

        $objComposer = json_decode($content);

        // Add scripts.unit-tests to composer.json
        $objComposer->scripts->{'unit-tests'} = '@php tools/phpunit/vendor/bin/phpunit -c tools/phpunit/phpunit.xml.dist';

        // Encode and save composer.json
        $content = json_encode($objComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->fileStorage->replaceContent($content);
    }
}
