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

final class AddStandardTagsMaker extends AbstractMaker
{
    public const PRIORITY = 10000;

    public static function getSubscribedEvents(): array
    {
        return [
            // This subscriber should be executed first!!!
            AddTagsEvent::NAME => ['addTagsToStorage', self::PRIORITY],
            AddMakerEvent::NAME => ['addFilesToStorage', self::PRIORITY],
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

        /** @var Str $strAdapter */
        $strAdapter = $this->framework->getAdapter(Str::class);

        // Namespaces
        $this->tagStorage->set('toplevelnamespace', $strAdapter->asClassName((string) $this->input->vendorname));
        $this->tagStorage->set('sublevelnamespace', $strAdapter->asClassName((string) $this->input->repositoryname));

        // Current year
        $this->tagStorage->set('year', date('Y'));

        // Phpdoc
        $strPhpdoc = $this->fileStorage->getTagReplacedContentFromFilePath(sprintf('%s/partials/phpdoc.tpl.txt', $this->skeletonPath), $this->tagStorage);
        $this->tagStorage->set('phpdoc', $strAdapter->generateHeaderCommentFromString($strPhpdoc));
        $phpdoclines = explode(PHP_EOL, $strPhpdoc);
        $ecsphpdoc = preg_replace("/[\r\n|\n]+/", '', implode('', array_map(static fn ($line) => $line.'\n', $phpdoclines)));
        $this->tagStorage->set('ecsphpdoc', rtrim($ecsphpdoc, '\\n'));
    }

    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);
    }
}
