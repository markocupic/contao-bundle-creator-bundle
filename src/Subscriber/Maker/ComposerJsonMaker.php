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

final class ComposerJsonMaker extends AbstractMaker
{
    public const PRIORITY = 1000;

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

        $this->tagStorage->set('composerdescription', (string) $this->input->composerdescription);
        $this->tagStorage->set('composerlicense', (string) $this->input->composerlicense);
        $this->tagStorage->set('composerauthorname', (string) $this->input->composerauthorname);
        $this->tagStorage->set('composerauthoremail', (string) $this->input->composerauthoremail);
        $this->tagStorage->set('composerauthorwebsite', (string) $this->input->composerauthorwebsite);
    }

    /**
     * Add the composer.json file to file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        $source = sprintf(
            '%s/composer.tpl.json',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/composer.json',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        $content = $this->fileStorage->getContent();
        $objComposer = json_decode($content);

        // Name
        $objComposer->name = $this->input->vendorname.'/'.$this->input->repositoryname;

        // Description
        $objComposer->description = $this->input->composerdescription;

        // License
        $objComposer->license = $this->input->composerlicense;

        //Authors
        if (!isset($objComposer->authors) && !\is_array($objComposer->authors)) {
            $objComposer->authors = [];
        }
        $authors = new \stdClass();
        $authors->name = $this->input->composerauthorname;
        $authors->email = $this->input->composerauthoremail;
        $authors->homepage = $this->input->composerauthorwebsite;
        $authors->role = 'Developer';
        $objComposer->authors[] = $authors;

        // Support
        if (!isset($objComposer->support) && !\is_object($objComposer->support)) {
            $objComposer->support = new \stdClass();
        }

        $objComposer->support->issues = sprintf(
            'https://github.com/%s/%s/issues',
            $this->input->vendorname,
            $this->input->repositoryname
        );

        $objComposer->support->source = sprintf(
            'https://github.com/%s/%s',
            $this->input->vendorname,
            $this->input->repositoryname
        );

        // Version composerpackageversion
        if (!empty(trim((string) $this->input->composerpackageversion))) {
            $objComposer->version = trim((string) $this->input->composerpackageversion);
        }

        // Add contao/easy-coding-standard
        if ($this->input->addEasyCodingStandard) {
            $objComposer->{'require-dev'}->{'contao/easy-coding-standard'} = '^3.0';
        }

        // Autoload
        if (!isset($objComposer->autoload) && !\is_object($objComposer->autoload)) {
            $objComposer->autoload = new \stdClass();
        }

        // Autoload.psr-4
        if (!isset($objComposer->autoload->{'psr-4'}) && !\is_object($objComposer->autoload->{'psr-4'})) {
            $objComposer->autoload->{'psr-4'} = new \stdClass();
        }

        $psr4Key = sprintf(
            '%s\\%s\\',
            $this->tagStorage->get('toplevelnamespace'),
            $this->tagStorage->get('sublevelnamespace')
        );

        $objComposer->autoload->{'psr-4'}->{$psr4Key} = 'src/';

        // Extra
        if (!isset($objComposer->extra) && !\is_object($objComposer->extra)) {
            $objComposer->extra = new \stdClass();
        }

        $objComposer->extra->{'contao-manager-plugin'} = sprintf(
            '%s\%s\ContaoManager\Plugin',
            $this->tagStorage->get('toplevelnamespace'),
            $this->tagStorage->get('sublevelnamespace')
        );

        $content = json_encode($objComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->fileStorage->replaceContent($content);
    }
}
