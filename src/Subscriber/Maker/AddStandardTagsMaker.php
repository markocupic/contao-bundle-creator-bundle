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

class AddStandardTagsMaker extends AbstractMaker implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // This subscriber should be executed first!!!
            AddTagsEvent::NAME => ['addTagsToStorage', 10000],
            AddMakerEvent::NAME => ['addFilesToStorage', 10000],
        ];
    }

    /**
     * Add some default tags and the phpdoc tac to the tag storage.
     *
     * @throws \Exception
     */
    public function addTagsToStorage(AddTagsEvent $event): void
    {
        parent::addTagsToStorage($event);

        // Store input values into the tag storage
        foreach ($this->input->row() as $fieldname => $value) {
            $this->tagStorage->set((string) $fieldname, (string) $value);
        }

        // Namespaces
        $this->tagStorage->set('toplevelnamespace', Str::asClassName((string) $this->input->vendorname));
        $this->tagStorage->set('sublevelnamespace', Str::asClassName((string) $this->input->repositoryname));

        // Current year
        $this->tagStorage->set('year', date('Y'));

        // Phpdoc
        $strPhpdoc = $this->fileStorage->getTagReplacedContentFromFilePath(sprintf('%s/partials/phpdoc.tpl.txt', $this->skeletonPath), $this->tagStorage);
        $this->tagStorage->set('phpdoc', Str::generateHeaderCommentFromString($strPhpdoc));
        $phpdoclines = explode(PHP_EOL, $strPhpdoc);
        $ecsphpdoc = preg_replace("/[\r\n|\n]+/", '', implode('', array_map(static function ($line) {return $line.'\n'; }, $phpdoclines)));
        $this->tagStorage->set('ecsphpdoc', rtrim($ecsphpdoc, '\\n'));
    }

    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);
    }
}
