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

use Contao\Controller;
use Contao\StringUtil;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContaoBackendModuleMaker extends AbstractMaker implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AddTagsEvent::NAME => ['addTagsToStorage', 930],
            AddMakerEvent::NAME => ['addFilesToStorage', 930],
        ];
    }

    public function addTagsToStorage(AddTagsEvent $event): void
    {
        parent::addTagsToStorage($event);

        if (!$this->input->addBackendModule || empty($this->input->dcatable)) {
            return;
        }

        // Add tags
        $controllerAdapter = $this->framework->getAdapter(Controller::class);
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);

        $controllerAdapter->loadDataContainer($this->input->dcatable);

        if (class_exists($this->input->dcatable)) {
            $this->tagStorage->set('dcaclassname', (string) $this->input->dcatable.'_custom');
        } else {
            $this->tagStorage->set('dcaclassname', (string) $this->input->dcatable);
        }
        $this->tagStorage->set('dcatable', (string) $this->input->dcatable);
        $this->tagStorage->set('modelclassname', (string) Str::asContaoModelClassName((string) $this->input->dcatable));
        $this->tagStorage->set('backendmoduletype', (string) $this->input->backendmoduletype);
        $this->tagStorage->set('backendmodulecategory', (string) $this->input->backendmodulecategory);
        $arrLabel = $stringUtilAdapter->deserialize($this->input->backendmoduletrans, true);
        $this->tagStorage->set('backendmoduletrans_0', $arrLabel[0]);
        $this->tagStorage->set('backendmoduletrans_1', $arrLabel[1]);
    }

    /**
     * Add backend module files to file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        if (!$this->input->addBackendModule || empty($this->input->dcatable)) {
            return;
        }

        // Add dca table file
        $source = sprintf(
            '%s/src/Resources/contao/dca/tl_sample_table.tpl.php',
            $this->skeletonPath)
        ;

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/dca/%s.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            $this->input->dcatable
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add dca table translation file
        $source = sprintf(
            '%s/src/Resources/contao/languages/en/tl_sample_table.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/languages/en/%s.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            $this->input->dcatable
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add a sample model
        $source = sprintf(
            '%s/src/Model/Model.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Model/%s.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            Str::asContaoModelClassName((string) $this->input->dcatable)
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add src/Resources/contao/languages/en/modules.php to file storage
        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/languages/en/modules.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        $source = sprintf(
            '%s/src/Resources/contao/languages/en/modules.tpl.php',
            $this->skeletonPath
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add src/Resources/contao/languages/en/default.php to file storage
        $target = sprintf(
            '%s/vendor/%s/%s/src/Resources/contao/languages/en/default.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        $source = sprintf(
            '%s/src/Resources/contao/languages/en/default.tpl.php',
            $this->skeletonPath
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }
    }
}
