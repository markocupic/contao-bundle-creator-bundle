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

        $this->tagStorage->set('fullyquallifiedfrontendmoduleclassname', \sprintf('%s\%s\Controller\FrontendModule\%s', $toplevelnamespace, $sublevelnamespace, $frontendmoduleclassname));
        $this->tagStorage->set('frontendmoduleclassname', $frontendmoduleclassname);
        $this->tagStorage->set('frontendmoduletype', (string) $this->input->frontendmoduletype);
        $this->tagStorage->set('frontendmodulecategory', (string) $this->input->frontendmodulecategory);
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

        // Add the frontend module class to src/Controller/FrontendModuleController
        $source = \sprintf(
            '%s/src/Controller/FrontendModule/FrontendModuleController.php.ttpl',
            $this->skeletonPath,
        );

        $target = \sprintf(
            '%s/vendor/%s/%s/src/Controller/FrontendModule/%s.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            $strFrontendModuleClassname,
        );

        if (!$this->fileStorage->has($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add the content element template
        $source = \sprintf(
            '%s/contao/templates/twig/frontend_module/sample_module.html.twig.ttpl',
            $this->skeletonPath,
        );

        $target = \sprintf(
            '%s/vendor/%s/%s/contao/templates/twig/frontend_module/%s.html.twig',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
            $strFrontenModuleTemplateName,
        );

        if (!$this->fileStorage->has($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add the .twig-root file to the content element template directory
        $source = \sprintf(
            '%s/contao/templates/twig/.twig-root',
            $this->skeletonPath,
        );

        $target = \sprintf(
            '%s/vendor/%s/%s/contao/templates/twig/.twig-root',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        if (!$this->fileStorage->has($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add contao/dca/tl_module.php
        $source = \sprintf(
            '%s/contao/dca/tl_module.php.ttpl',
            $this->skeletonPath,
        );

        $target = \sprintf(
            '%s/vendor/%s/%s/contao/dca/tl_module.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        if (!$this->fileStorage->has($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add contao/languages/en/modules.php to file storage
        $target = \sprintf(
            '%s/vendor/%s/%s/contao/languages/en/modules.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        $source = \sprintf(
            '%s/contao/languages/en/modules.php.ttpl',
            $this->skeletonPath,
        );

        if (!$this->fileStorage->has($target)) {
            $this->fileStorage->addFile($source, $target);
        }

        // Add contao/languages/en/default.php to file storage
        $target = \sprintf(
            '%s/vendor/%s/%s/contao/languages/en/default.php',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        $source = \sprintf(
            '%s/contao/languages/en/default.php.ttpl',
            $this->skeletonPath,
        );

        if (!$this->fileStorage->has($target)) {
            $this->fileStorage->addFile($source, $target);
        }
    }
}
