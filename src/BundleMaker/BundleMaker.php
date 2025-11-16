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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Markocupic\ContaoBundleCreatorBundle\Skeleton;
use Markocupic\ZipBundle\Zip\Zip;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Yaml\Yaml;

class BundleMaker
{
    protected ContaoBundleCreatorModel|null $input = null;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly RequestStack $requestStack,
        private readonly FileStorage $fileStorage,
        private readonly TagStorage $tagStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Message $message,
        private readonly Zip $zip,
        private readonly string $projectDir,
    ) {
    }

    /**
     * Run the contao bundle creator.
     *
     * @throws \Exception
     */
    public function run(ContaoBundleCreatorModel $input): void
    {
        $this->input = $input;

        if ($this->bundleExists() && !$this->input->overwriteexisting) {
            $this->message->addError('An extension with the same name already exists. Please set the "override extension flag".');

            return;
        }

        // Create a backup of the old bundle that will be overwritten now
        if ($this->bundleExists()) {
            $this->createBackup();
        }

        $this->message->addInfo(\sprintf('Started generating "%s/%s" bundle.', $this->input->vendorname, $this->input->repositoryname));

        /*
         * Keep the application extensible.
         * Write maker classes to add tags & files to the bundle.
         * Store maker classes in src/EventSubscriber/Maker and
         * implement these makers as event subscribers.
         *
         * 1. Add all the necessary tags to the tag storage.
         */
        $event = new AddTagsEvent($this->framework, $this->requestStack, $this->tagStorage, $this->fileStorage, $this->input, $this->message, Skeleton::getDefaultPath(), $this->projectDir);
        $this->eventDispatcher->dispatch($event, AddTagsEvent::NAME);

        /*
         * 2. Add all the files to a virtual file storage.
         */
        $event = new AddMakerEvent($this->framework, $this->requestStack, $this->tagStorage, $this->fileStorage, $this->input, $this->message, Skeleton::getDefaultPath(), $this->projectDir);
        $this->eventDispatcher->dispatch($event, AddMakerEvent::NAME);

        /*
         * 3. Replace tags in the file storage.
         */
        $this->replaceTags();

        /*
         * 4. Check yaml files.
         */
        $this->checkYamlFiles();

        /*
         * 5. Copy all the bundle files from the virtual storage to the destination directories in vendor/vendorname/bundlename.
         */
        $this->writeBundleFiles();

        /*
         * 6. Store the new bundle also as a zip-package in system/tmp for downloading it after the generating process.
         */
        $this->generateZipArchive();
    }

    /**
     * Check if an extension with the same name already exists.
     */
    protected function bundleExists(): bool
    {
        return is_dir($this->projectDir.'/vendor/'.$this->input->vendorname.'/'.$this->input->repositoryname);
    }

    protected function createBackup(): void
    {
        $zipSource = \sprintf(
            '%s/vendor/%s/%s',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        $zipTarget = \sprintf(
            '%s/system/tmp/%s.zip',
            $this->projectDir,
            $this->input->repositoryname.'_backup_'.Date::parse('Y-m-d_H-i-s', time()),
        );

        $this->zip
            ->stripSourcePath($zipSource)
            ->addDirRecursive($zipSource)
            ->run($zipTarget)
        ;
    }

    protected function generateZipArchive(): void
    {
        // Do not create the bundle if there is an error.
        if ($this->message->hasError()) {
            return;
        }

        // Store the new bundle also as a zip-package in system/tmp for downloading it after the generating process
        $zipSource = \sprintf(
            '%s/vendor/%s/%s',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname,
        );

        $zipTarget = \sprintf(
            '%s/system/tmp/%s-main.zip',
            $this->projectDir,
            $this->input->repositoryname,
        );

        $zip = $this->zip
            ->ignoreDotFiles(false)
            ->stripSourcePath($this->projectDir.'/vendor/'.$this->input->vendorname)
            ->addDirRecursive($zipSource)
        ;

        if ($zip->run($zipTarget)) {
            $session = $this->requestStack->getCurrentRequest()->getSession();
            $session->set('CONTAO-BUNDLE-CREATOR.LAST-ZIP', str_replace($this->projectDir.'/', '', $zipTarget));
        }
    }

    /**
     * Replace tags in file storage.
     */
    protected function replaceTags(): void
    {
        // Do not create the bundle if there is an error.
        if ($this->message->hasError()) {
            return;
        }

        foreach ($this->fileStorage->getAll() as $arrFile) {
            if ($this->fileStorage->has($arrFile['target'])) {
                $this->fileStorage
                    ->getFile($arrFile['target'])
                    ->replaceTags($this->tagStorage, ['ttpl'])
                ;
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function checkYamlFiles(): void
    {
        // Do not create the bundle if there is an error.
        if ($this->message->hasError()) {
            return;
        }

        /** @var Yaml $yamlAdapter */
        $yamlAdapter = $this->framework->getAdapter(Yaml::class);

        foreach ($this->fileStorage->getAll() as $arrFile) {
            if ($this->fileStorage->has($arrFile['target'])) {
                $info = new \SplFileInfo($arrFile['target']);

                if ('yaml' === $info->getExtension() || 'yml' === $info->getExtension()) {
                    $strYaml = $this->fileStorage
                        ->getFile($arrFile['target'])
                        ->getContent()
                    ;

                    // Validate yaml files
                    $yamlAdapter->parse($strYaml);
                }
            }
        }
    }

    /**
     * Write files from the file storage to the filesystem.
     */
    protected function writeBundleFiles(): void
    {
        // Do not generate the bundle if there is an error.
        if ($this->message->hasError()) {
            return;
        }

        foreach ($this->fileStorage->getAll() as $arrFile) {
            try {
                $this->fileStorage->createFile($arrFile['target']);
                $this->message->addInfo(\sprintf('Created file "%s".', $arrFile['target']));
            } catch (\Exception $e) {
                // Display a message in the backend
                $this->message->addError(\sprintf('Could not create file "%s".', $arrFile['target']));
            }
        }

        // Display message in the backend
        $this->message->addConfirmation('Added one or more files to the bundle. Please run at least "composer install" or even "composer update", if you have made changes to the root composer.json.');
    }
}
