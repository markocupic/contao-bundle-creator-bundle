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

use Contao\Date;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;

final class AlterRootComposerJsonMaker extends AbstractMaker
{
    const PRIORITY = -10000;

    public static function getSubscribedEvents(): array
    {
        return [
            // This subscriber should be executed last!!!
            AddTagsEvent::NAME => ['addTagsToStorage', self::PRIORITY],
            AddMakerEvent::NAME => ['addFilesToStorage', self::PRIORITY],
        ];
    }

    public function addTagsToStorage(AddTagsEvent $event): void
    {
        parent::addTagsToStorage($event);
    }

    /**
     * Alter root composer.json.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        if (!$this->input->editRootComposer) {
            return;
        }

        $blnModified = false;
        $content = file_get_contents($this->projectDir.'/composer.json');
        $objJSON = json_decode($content);

        if (!isset($objJSON->repositories)) {
            $objJSON->repositories = [];
        }

        $objRepositories = new \stdClass();

        if ('path' === $this->input->rootcomposerextendrepositorieskey) {
            // Check if a package version is set.
            if (empty(trim((string) $this->input->composerpackageversion))) {
                $this->message->addError('Package version can not be empty if you selected "path" in the "repositories" key of your root composer.json file.');

                return;
            }

            $objRepositories->type = 'path';
            $objRepositories->url = sprintf(
                    'vendor/%s/%s',
                    $this->input->vendorname,
                    $this->input->repositoryname
                );

            // Prevent duplicate entries
            if (!\in_array($objRepositories, $objJSON->repositories, false)) {
                $blnModified = true;
                $objJSON->repositories[] = $objRepositories;
                $this->message->addInfo('Extended the repositories section in the root composer.json. Please check!');
            }
        }

        if ('vcs-github' === $this->input->rootcomposerextendrepositorieskey) {
            $objRepositories->type = 'vcs';
            $objRepositories->url = sprintf(
                    'https://github.com/%s/%s',
                    $this->input->vendorname,
                    $this->input->repositoryname
                );

            // Prevent duplicate entries
            if (!\in_array($objRepositories, $objJSON->repositories, false)) {
                $blnModified = true;
                $objJSON->repositories[] = $objRepositories;
                $this->message->addInfo('Extended the repositories section in the root composer.json. Please check!');
            }
        }

        // Extend require key
        $blnModified = true;
        $objJSON->require->{sprintf('%s/%s', $this->input->vendorname, $this->input->repositoryname)} = 'dev-main';
        $this->message->addInfo('Extended the require section in the root composer.json. Please check!');

        if ($blnModified) {
            /** @var Date $dateAdapter */
            $dateAdapter = $this->framework->getAdapter(Date::class);

            // Make a backup first
            $strBackupPath = sprintf(
                'system/tmp/composer_backup_%s.json',
                $dateAdapter->parse('Y-m-d _H-i-s', time())
            );

            copy(
                $this->projectDir.\DIRECTORY_SEPARATOR.'composer.json',
                $this->projectDir.\DIRECTORY_SEPARATOR.$strBackupPath
            );

            $this->message->addInfo(sprintf('Created backup of composer.json in "%s"', $strBackupPath));

            // Append modifications
            $content = json_encode($objJSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $source = sprintf(
                '%s/composer.json',
                $this->projectDir
            );

            $target = $source;

            if (!$this->fileStorage->hasFile($target)) {
                $this->fileStorage->addFile($source, $target);
            }

            $this->fileStorage
                ->getFile($target)
                ->replaceContent($content)
            ;
        }
    }
}
