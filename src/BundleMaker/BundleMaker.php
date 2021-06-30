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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Markocupic\ZipBundle\Zip\Zip;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BundleMaker.
 */
class BundleMaker
{
    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var FileStorage
     */
    protected $fileStorage;

    /**
     * @var TagStorage
     */
    protected $tagStorage;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var Zip
     */
    protected $zip;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var ContaoBundleCreatorModel
     */
    protected $input;

    /**
     * @var string
     */
    protected $skeletonPath;

    /**
     * BundleMaker constructor.
     */
    public function __construct(ContaoFramework $framework, Session $session, FileStorage $fileStorage, TagStorage $tagStorage, EventDispatcherInterface $eventDispatcher, Message $message, Zip $zip, string $projectDir)
    {
        $this->framework = $framework;
        $this->session = $session;
        $this->fileStorage = $fileStorage;
        $this->tagStorage = $tagStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->message = $message;
        $this->zip = $zip;
        $this->projectDir = $projectDir;
        $this->skeletonPath = realpath(__DIR__.'/../Resources/skeleton');
    }

    /**
     * Run contao bundle creator.
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

        $this->message->addInfo(sprintf('Started generating "%s/%s" bundle.', $this->input->vendorname, $this->input->repositoryname));

        /*
         * Keep the application extensible.
         * Add maker classes to add tags & files to the bundle.
         * Store maker classes in src/Subscriber/Maker and
         * implement these makers as event subscribers.
         *
         * 1. Add all the necessary tags to the tag storage.
         */
        $this->eventDispatcher->dispatch(new AddTagsEvent((object) get_object_vars($this)), AddTagsEvent::NAME);

        /*
         * 2. Add all the files to a virtual file storage.
         */
        $this->eventDispatcher->dispatch(new AddMakerEvent((object) get_object_vars($this)), AddMakerEvent::NAME);

        /*
         * 3. Replace tags in file storage.
         */
        $this->replaceTags();

        /*
         * 4. Check yaml/yml files.
         */
        $this->checkYamlFiles();

        /*
         * 5. Copy all the bundle files from the virtual storage to the destination directories in vendor/vendorname/bundlename.
         */
        $this->writeBundleFiles();

        /*
         * 6. Store new bundle also as a zip-package in system/tmp for downloading it after the generating process.
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
        $zipSource = sprintf(
            '%s/vendor/%s/%s',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        $zipTarget = sprintf(
            '%s/system/tmp/%s.zip',
            $this->projectDir,
            $this->input->repositoryname.'_backup_'.Date::parse('Y-m-d_H-i-s', time())
        );

        $this->zip
            ->stripSourcePath($zipSource)
            ->addDirRecursive($zipSource)
            ->run($zipTarget)
        ;
    }

    protected function generateZipArchive(): void
    {
        // Do not create the bundle, if there is an error.
        if ($this->message->hasError()) {
            return;
        }

        // Store new bundle also as a zip-package in system/tmp for downloading it after the generating process
        $zipSource = sprintf(
            '%s/vendor/%s/%s',
            $this->projectDir,
            $this->input->vendorname,
            $this->input->repositoryname
        );

        $zipTarget = sprintf(
            '%s/system/tmp/%s.zip',
            $this->projectDir,
            $this->input->repositoryname
        );

        $zip = $this->zip
            ->ignoreDotFiles(false)
            ->stripSourcePath($zipSource)
            ->addDirRecursive($zipSource)
        ;

        if ($zip->run($zipTarget)) {
            $this->session->set('CONTAO-BUNDLE-CREATOR.LAST-ZIP', str_replace($this->projectDir.'/', '', $zipTarget));
        }
    }

    /**
     * Replace tags in file storage.
     */
    protected function replaceTags(): void
    {
        // Do not create the bundle, if there is an error.
        if ($this->message->hasError()) {
            return;
        }

        foreach ($this->fileStorage->getAll() as $arrFile) {
            if ($this->fileStorage->hasFile($arrFile['target'])) {
                $this->fileStorage
                    ->getFile($arrFile['target'])
                    ->replaceTags($this->tagStorage, ['.tpl.'])
                    ;
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function checkYamlFiles(): void
    {
        // Do not create the bundle, if there is an error.
        if ($this->message->hasError()) {
            return;
        }

        /** @var Yaml $yamlAdapter */
        $yamlAdapter = $this->framework->getAdapter(Yaml::class);

        foreach ($this->fileStorage->getAll() as $arrFile) {
            if ($this->fileStorage->hasFile($arrFile['target'])) {
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
        // Do not create the bundle, if there is an error.
        if ($this->message->hasError()) {
            return;
        }

        foreach ($this->fileStorage->getAll() as $arrFile) {
            if (false !== $this->fileStorage->createFile($arrFile['target'])) {
                // Display message in the backend
                $this->message->addInfo(sprintf('Created file "%s".', $arrFile['target']));
            } else {
                // Display message in the backend
                $this->message->addError(sprintf('Could not create file "%s".', $arrFile['target']));
            }
        }

        // Display message in the backend
        $this->message->addConfirmation('Added one or more files to the bundle. Please run at least "composer install" or even "composer update", if you have made changes to the root composer.json.');
    }
}
