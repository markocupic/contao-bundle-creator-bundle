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

class DependencyInjectionExtensionClassMaker extends AbstractMaker implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AddTagsEvent::NAME => ['addTagsToStorage', 980],
            AddMakerEvent::NAME => ['addFilesToStorage', 980],
        ];
    }

    public function addTagsToStorage(AddTagsEvent $event): void
    {
        parent::addTagsToStorage($event);

        $this->tagStorage->set('dependencyinjectionextensionclassname', Str::asDependencyInjectionExtensionClassName((string) $this->input->vendorname, (string) $this->input->repositoryname));
    }

    /**
     * Add the Dependency Injection Extension class to file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        $source = sprintf(
            '%s/src/DependencyInjection/Extension.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/DependencyInjection/%s.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            Str::asDependencyInjectionExtensionClassName((string) $this->input->vendorname, (string) $this->input->repositoryname)
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }
    }
}
