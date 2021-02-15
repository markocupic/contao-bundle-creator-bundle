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

        // Keep the application extensible.
        // Add maker classes to add tags & files to the bundle.
        // Store maker classes in src/Subscriber/Maker
        // Implement these makers as event subscribers.
        $this->eventDispatcher->dispatch(new AddTagsEvent((object) get_object_vars($this)), AddTagsEvent::NAME);
        $this->eventDispatcher->dispatch(new AddMakerEvent((object) get_object_vars($this)), AddMakerEvent::NAME);

        // Replace tags in file storage
        $this->replaceTags();

        // Check yaml files
        $this->checkYamlFiles();

        // Copy all the bundle files from the storage to the destination directories in vendor/vendorname/bundlename
        $this->createBundleFiles();

        // Store new bundle also as a zip-package in system/tmp for downloading it after the generating process
        $this->generateZipArchive();

        // Optionally extend the composer.json file located in the root directory
        $this->editRootComposerJson();
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
        foreach ($this->fileStorage->getAll() as $arrFile) {
            if ($this->fileStorage->hasFile($arrFile['target'])) {
                $this->fileStorage
                    ->getFile($arrFile['target'])
                    ->replaceTags($this->tagStorage, ['.tpl.'])
                    ;
            }
        }
    }

    protected function checkYamlFiles(): void
    {
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

                    // Try to parse yaml file
                    try {
                        $yamlAdapter->parse($strYaml);
                    } catch (ParseException $exception) {
                        throw new ParseException(sprintf('Unable to parse the YAML string in %s: %s', $arrFile['target'], $exception->getMessage()));
                    }
                }
            }
        }

        // Validate config files
        try {
            $arrYaml = $yamlAdapter->parse($strYaml);

            if ('listener.tpl.yml' === $file || 'services.tpl.yml' === $file) {
                if (!\array_key_exists('services', $arrYaml)) {
                    throw new ParseException('Key "services" not found. Please check the indents.');
                }
            }

            if ('parameters.tpl.yml' === $file) {
                if (!\array_key_exists('parameters', $arrYaml)) {
                    throw new ParseException('Key "parameters" not found. Please check the indents.');
                }
            }
        } catch (ParseException $exception) {
            throw new ParseException(sprintf('Unable to parse the YAML string in %s: %s', $target, $exception->getMessage()));
        }
    }

    /**
     * Write files from the file storage to the filesystem.
     */
    protected function createBundleFiles(): void
    {
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
        $this->message->addInfo('Added one or more files to the bundle. Please run at least "composer install" or even "composer update", if you have made changes to the root composer.json.');
    }

    /**
     * Optionally edit the composer.json file located in the root directory.
     *
     * @throws \Exception
     */
    protected function editRootComposerJson(): void
    {
        $blnModified = false;

        $content = file_get_contents($this->projectDir.'/composer.json');
        $objJSON = json_decode($content);

        if ($this->input->editRootComposer) {
            if (!isset($objJSON->repositories)) {
                $objJSON->repositories = [];
            }

            $objRepositories = new \stdClass();

            if ('path' === $this->input->rootcomposerextendrepositorieskey) {
                $objRepositories->type = 'path';
                $objRepositories->url = sprintf(
                    'vendor/%s/%s',
                    $this->input->vendorname,
                    $this->input->repositoryname
                );

                // Prevent duplicate entries
                if (!\in_array($objRepositories, $objJSON->repositories, false)) {
                    $blnModified = true;
                    $objJSON->repositories[] = $objRepositories;
                    $this->message->addInfo('Extended the repositories section in the root composer.json. Please check!');
                }
            }

            if ('vcs-github' === $this->input->rootcomposerextendrepositorieskey) {
                $objRepositories->type = 'vcs';
                $objRepositories->url = sprintf(
                    'https://github.com/%s/%s',
                    $this->input->vendorname,
                    $this->input->repositoryname
                );

                // Prevent duplicate entries
                if (!\in_array($objRepositories, $objJSON->repositories, false)) {
                    $blnModified = true;
                    $objJSON->repositories[] = $objRepositories;
                    $this->message->addInfo('Extended the repositories section in the root composer.json. Please check!');
                }
            }

            // Extend require key
            $blnModified = true;
            $objJSON->require->{sprintf('%s/%s', $this->input->vendorname, $this->input->repositoryname)} = 'dev-main';
            $this->message->addInfo('Extended the require section in the root composer.json. Please check!');
        }

        if ($blnModified) {
            // Make a backup first
            $strBackupPath = sprintf(
                'system/tmp/composer_backup_%s.json',
                Date::parse('Y-m-d _H-i-s', time())
            );

            copy(
                $this->projectDir.\DIRECTORY_SEPARATOR.'composer.json',
                $this->projectDir.\DIRECTORY_SEPARATOR.$strBackupPath
            );

            $this->message->addInfo(sprintf('Created backup of composer.json in "%s"', $strBackupPath));

            // Append modifications
            $content = json_encode($objJSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            file_put_contents($this->projectDir.'/composer.json', $content);
        }
    }
}
