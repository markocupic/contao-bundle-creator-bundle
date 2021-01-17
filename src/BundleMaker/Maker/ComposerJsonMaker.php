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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Maker;

class ComposerJsonMaker extends AbstractMaker
{
    /**
     * @throws \Exception
     */
    public function addFilesToStorage(): void
    {
        $source = sprintf(
            '%s/composer.tpl.json',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/composer.json',
            $this->projectDir,
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );

        $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage, ['.tpl.']);

        $content = $this->fileStorage->getContent();
        $objComposer = json_decode($content);

        // Name
        $objComposer->name = $this->arrInput['vendorname'].'/'.$this->arrInput['repositoryname'];

        // Description
        $objComposer->description = $this->arrInput['composerdescription'];

        // License
        $objComposer->license = $this->arrInput['composerlicense'];

        //Authors
        if (!isset($objComposer->authors) && !\is_array($objComposer->authors)) {
            $objComposer->authors = [];
        }
        $authors = new \stdClass();
        $authors->name = $this->arrInput['composerauthorname'];
        $authors->email = $this->arrInput['composerauthoremail'];
        $authors->homepage = $this->arrInput['composerauthorwebsite'];
        $authors->role = 'Developer';
        $objComposer->authors[] = $authors;

        // Support
        if (!isset($objComposer->support) && !\is_object($objComposer->support)) {
            $objComposer->support = new \stdClass();
        }
        $objComposer->support->issues = sprintf(
            'https://github.com/%s/%s/issues',
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname']
        );
        $objComposer->support->source = sprintf(
            'https://github.com/%s/%s',
            $this->arrInput['vendorname'],
            $this->arrInput['repositoryname'],
        );

        // Version
        if ($this->tagStorage->has('composerpackageversion')) {
            $objComposer->version = $this->arrInput['composerpackageversion'];
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
            $this->arrInput['toplevelnamespace'],
            $this->arrInput['sublevelnamespace']
        );
        $objComposer->autoload->{'psr-4'}->{$psr4Key} = 'src/';

        // Extra
        if (!isset($objComposer->extra) && !\is_object($objComposer->extra)) {
            $objComposer->extra = new \stdClass();
        }
        $objComposer->extra->{'contao-manager-plugin'} = sprintf(
            '%s\%s\ContaoManager\Plugin',
            $this->arrInput['toplevelnamespace'],
            $this->arrInput['sublevelnamespace']
        );

        $content = json_encode($objComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->fileStorage->replaceContent($content);
    }
}
