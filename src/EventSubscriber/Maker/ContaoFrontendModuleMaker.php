<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\EventSubscriber\Maker;

use Contao\StringUtil;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;

final class ContaoFrontendModuleMaker extends AbstractMaker
{
    public const PRIORITY = 920;

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

        if (!$this->input->addFrontendModule) {
            return;
        }

        /** @var Str $strAdapter */
        $strAdapter = $this->framework->getAdapter(Str::class);

        $stringUtilAdaper = $this->framework->getAdapter(StringUtil::class);

        $toplevelnamespace = $strAdapter->asClassName((string) $this->input->vendorname);
        $sublevelnamespace = $strAdapter->asClassName((string) $this->input->repositoryname);
        $frontendmoduleclassname = $strAdapter->asContaoFrontendModuleClassName((string) $this->input->frontendmoduletype);

        $this->tagStorage->set('fullyquallifiedfrontendmoduleclassname', sprintf('%s\%s\Controller\FrontendModule\%s', $toplevelnamespace, $sublevelnamespace, $frontendmoduleclassname));
        $this->tagStorage->set('frontendmoduleclassname', $frontendmoduleclassname);
        $this->tagStorage->set('frontendmoduletype', (string) $this->input->frontendmoduletype);
        $this->tagStorage->set('frontendmodulecategory', (string) $this->input->frontendmodulecategory);
        $this->tagStorage->set('frontendmoduletemplate', $strAdapter->asContaoFrontendModuleTemplateName((string) $this->input->frontendmoduletype));
        $arrLabel = $stringUtilAdaper->deserialize($this->input->frontendmoduletrans, true);
        $this->tagStorage->set('frontendmoduletrans_0', $arrLabel[0]);
        $this->tagStorage->set('frontendmoduletrans_1', $arrLabel[1]);
    }

    /**
     * Add frontend module files to file storage.
     *
     * @throws \Exception
     */
    public function addFilesToStorage(AddMakerEvent $event): void
    {
        parent::addFilesToStorage($event);

        if (!$this->input->addFrontendModule) {
            return;
        }

        /** @var Str $strAdapter */
        $strAdapter = $this->framework->getAdapter(Str::class);

        // Get the frontend module template name
        $strFrontenModuleTemplateName = $strAdapter->asContaoFrontendModuleTemplateName((string) $this->input->frontendmoduletype);

        // Get the frontend module classname
        $strFrontendModuleClassname = $strAdapter->asContaoFrontendModuleClassName((string) $this->input->frontendmoduletype);

        // Add frontend module class to src/Controller/FrontendModuleController
        $source = sprintf(
            '%s/src/Controller/FrontendModule/FrontendModuleController.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/src/Controller/FrontendModule/%s.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            $strFrontendModuleClassname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add frontend module template
        $source = sprintf(
            '%s/contao/templates/mod_sample_module.tpl.html5',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/contao/templates/%s.html5',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            $strFrontenModuleTemplateName
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add contao/dca/tl_module.php
        $source = sprintf(
            '%s/contao/dca/tl_module.tpl.php',
            $this->skeletonPath
        );

        $target = sprintf(
            '%s/vendor/%s/%s/contao/dca/tl_module.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add contao/languages/en/modules.php to file storage
        $target = sprintf(
            '%s/vendor/%s/%s/contao/languages/en/modules.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        $source = sprintf(
            '%s/contao/languages/en/modules.tpl.php',
            $this->skeletonPath
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add contao/languages/en/default.php to file storage
        $target = sprintf(
            '%s/vendor/%s/%s/contao/languages/en/default.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        $source = sprintf(
            '%s/contao/languages/en/default.tpl.php',
            $this->skeletonPath
        );

        if (!$this->fileStorage->hasFile($target)) {
            $this->fileStorage->addFile($source, $target);
        }
    }
}
