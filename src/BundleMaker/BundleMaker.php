<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao Bundle Creator
 * @license    MIT
 * @see        https://github.com/markocupic/contao-bundle-creator-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker;

use Contao\Date;
use Contao\File;
use Contao\Files;
use Contao\StringUtil;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken\ParsePhpToken;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\SanitizeInput\SanitizeInput;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BundleMaker
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker
 */
class BundleMaker
{
    /** @var SessionInterface */
    protected $session;

    /** @var FileStorage */
    protected $fileStorage;

    /** @var TagStorage */
    protected $tagStorage;

    /** @var SanitizeInput */
    protected $sanitizeInput;

    /** @var Message */
    protected $message;

    /** @var string */
    protected $projectDir;

    /** @var ContaoBundleCreatorModel */
    protected $model;

    /** @var string */
    const SAMPLE_DIR = 'vendor/markocupic/contao-bundle-creator-bundle/src/Resources/skeleton/sample-repository';

    /**
     * BundleMaker constructor.
     *
     * @param Session $session
     * @param FileStorage $fileStorage
     * @param TagStorage $tagStorage
     * @param SanitizeInput $sanitizeInput
     * @param Message $message
     * @param string $projectDir
     */
    public function __construct(Session $session, FileStorage $fileStorage, TagStorage $tagStorage, SanitizeInput $sanitizeInput, Message $message, string $projectDir)
    {
        $this->session = $session;
        $this->fileStorage = $fileStorage;
        $this->tagStorage = $tagStorage;
        $this->sanitizeInput = $sanitizeInput;
        $this->message = $message;
        $this->projectDir = $projectDir;
    }

    /**
     * Run contao bundle creator
     *
     * @param ContaoBundleCreatorModel $model
     * @throws \Exception
     */
    public function run(ContaoBundleCreatorModel $model): void
    {
        $this->model = $model;

        if ($this->bundleExists() && !$this->model->overwriteexisting)
        {
            $this->message->addError('An extension with the same name already exists. Please set the "override extension flag".');
            return;
        }

        $this->message->addInfo(sprintf('Started generating "%s/%s" bundle.', $this->model->vendorname, $this->model->repositoryname));

        // Sanitize model (backendmoduletype, backendmodulecategory, frontendmoduletype, frontendmodulecategory)
        // Don't move the position, this has to be called first!
        $this->sanitizeModel();

        // Set the php template tags
        $this->setTags();

        // Add the composer.json file to file storage
        $this->addComposerJsonFileToFileStorage();

        // Add the bundle class to file storage
        $this->addBundleClassToFileStorage();

        // Add the Contao Manager Plugin class to file storage
        $this->addContaoManagerPluginClassToFileStorage();

        // Add unit tests to file storage
        $this->addUnitTestsToFileStorage();

        // Config files, assets, etc.
        $this->addMiscFilesToFileStorage();

        // Add backend module files to file storage
        if ($this->model->addBackendModule && $this->model->dcatable != '')
        {
            $this->addBackendModuleFilesToFileStorage();
        }

        // Add frontend module files to file storage
        if ($this->model->addFrontendModule)
        {
            $this->addFrontendModuleFilesToFileStorage();
        }

        // Add a custom route to the file storage
        if ($this->model->addCustomRoute)
        {
            $this->addCustomRouteToFileStorage();
        }

        // Create a backup of the old bundle that will be overwritten now
        if ($this->bundleExists())
        {
            $zipSource = sprintf('vendor/%s/%s', $this->model->vendorname, $this->model->repositoryname);
            $zipTarget = sprintf('system/tmp/%s.zip', $this->model->repositoryname . '_backup_' . Date::parse('Y-m-d _H-i-s', time()));
            $this->zipData($zipSource, $zipTarget);
        }

        // Replace if-tokens and replace simple tokens in file storage
        $this->parseTemplates();

        // Create all the bundle files in vendor/vendorname/bundlename
        $this->createFilesFromFileStorage();

        // Store new bundle also as a zip-package for downloading it from system/tmp
        $zipSource = sprintf('vendor/%s/%s', $this->model->vendorname, $this->model->repositoryname);
        $zipTarget = sprintf('system/tmp/%s.zip', $this->model->repositoryname);
        if ($this->zipData($zipSource, $zipTarget))
        {
            $this->session->set('CONTAO-BUNDLE-CREATOR.LAST-ZIP', $zipTarget);
        }

        // Optionally extend the composer.json file located in the root directory
        $this->editRootComposerJson();
    }

    /**
     * Check if an extension with the same name already exists
     *
     * @return bool
     */
    protected function bundleExists(): bool
    {
        return is_dir($this->projectDir . '/vendor/' . $this->model->vendorname . '/' . $this->model->repositoryname);
    }

    /**
     * Sanitize model
     *
     * @throws \Exception
     */
    protected function sanitizeModel(): void
    {
        if ($this->model->vendorname != '')
        {
            // Sanitize vendorname
            $this->model->vendorname = $this->sanitizeInput->getSanitizedVendorname((string) $this->model->vendorname);
            $this->model->save();
        }

        if ($this->model->repositoryname != '')
        {
            // Sanitize repositoryname
            $this->model->repositoryname = $this->sanitizeInput->getSanitizedRepositoryname((string) $this->model->repositoryname);
            $this->model->save();
        }

        if ($this->model->backendmoduletype != '')
        {
            // Get the backend module type and sanitize it to contao backend module convention
            $this->model->backendmoduletype = $this->sanitizeInput->getSanitizedBackendModuleType((string) $this->model->backendmoduletype);
            $this->model->save();
        }

        if ($this->model->dcatable != '')
        {
            // Sanitize dca table name
            $this->model->dcatable = $this->sanitizeInput->getSanitizedDcaTableName((string) $this->model->dcatable);
            $this->model->save();
        }

        if ($this->model->backendmodulecategory != '')
        {
            // Get the backend module category and sanitize it to contao backend module convention
            $this->model->backendmodulecategory = $this->sanitizeInput->toSnakecase((string) $this->model->backendmodulecategory);
            $this->model->save();
        }

        if ($this->model->frontendmoduletype != '')
        {
            // Get the frontend module type and sanitize it to contao frontend module convention
            $this->model->frontendmoduletype = $this->sanitizeInput->getSanitizedFrontendModuleType((string) $this->model->frontendmoduletype);
            $this->model->save();
        }

        if ($this->model->frontendmodulecategory != '')
        {
            // Get the frontend module category and sanitize it to contao frontend module convention
            $this->model->frontendmodulecategory = $this->sanitizeInput->toSnakecase((string) $this->model->frontendmodulecategory);
            $this->model->save();
        }
    }

    /**
     * Set all the tags here
     *
     * @throws \Exception
     * @todo add a contao hook
     */
    protected function setTags(): void
    {
        // Store model values into the tag storage
        $arrModel = $this->model->row();
        foreach ($arrModel as $fieldname => $value)
        {
            $this->tagStorage->set((string) $fieldname, (string) $value);
        }

        // Tags
        $this->tagStorage->set('vendorname', (string) $this->model->vendorname);
        $this->tagStorage->set('repositoryname', (string) $this->model->repositoryname);
        $this->tagStorage->set('vendornametolower', (string) str_replace('-', '_', strtolower($this->model->vendorname)));
        $this->tagStorage->set('repositorynametolower', (string) preg_replace('/-bundle$/', '', str_replace('-', '_', strtolower($this->model->repositoryname))));

        // Namespaces
        $this->tagStorage->set('toplevelnamespace', $this->sanitizeInput->toPsr4Namespace((string) $this->model->vendorname));
        $this->tagStorage->set('sublevelnamespace', $this->sanitizeInput->toPsr4Namespace((string) $this->model->repositoryname));

        // Twig namespace @Vendor/Bundlename
        $this->tagStorage->set('toplevelnamespacetwig', preg_replace('/Bundle$/', '', '@' . $this->sanitizeInput->toPsr4Namespace((string) $this->model->vendorname) . $this->sanitizeInput->toPsr4Namespace((string) $this->model->repositoryname)));

        // Composer
        $this->tagStorage->set('composerdescription', (string) $this->model->composerdescription);
        $this->tagStorage->set('composerlicense', (string) $this->model->composerlicense);
        $this->tagStorage->set('composerauthorname', (string) $this->model->composerauthorname);
        $this->tagStorage->set('composerauthoremail', (string) $this->model->composerauthoremail);
        $this->tagStorage->set('composerauthorwebsite', (string) $this->model->composerauthorwebsite);

        // Phpdoc
        $this->tagStorage->set('bundlename', (string) $this->model->bundlename);
        $this->tagStorage->set('phpdoc', $this->getContentFromPartialFile('phpdoc.tpl.txt'));
        $this->tagStorage->set('year', date('Y'));

        // Dca table and backend module
        if ($this->model->addBackendModule && $this->model->dcatable != '')
        {
            $this->tagStorage->set('dcatable', (string) $this->model->dcatable);
            $this->tagStorage->set('modelclassname', (string) $this->sanitizeInput->getSanitizedModelClassname((string) $this->model->dcatable));
            $this->tagStorage->set('backendmoduletype', (string) $this->model->backendmoduletype);
            $this->tagStorage->set('backendmodulecategory', (string) $this->model->backendmodulecategory);
            $arrLabel = StringUtil::deserialize($this->model->backendmoduletrans, true);
            $this->tagStorage->set('backendmoduletrans_0', $arrLabel[0]);
            $this->tagStorage->set('backendmoduletrans_1', $arrLabel[1]);
        }

        // Frontend module
        if ($this->model->addFrontendModule)
        {
            $this->tagStorage->set('frontendmoduleclassname', $this->sanitizeInput->getSanitizedFrontendModuleClassname((string) $this->model->frontendmoduletype));
            $this->tagStorage->set('frontendmoduletype', (string) $this->model->frontendmoduletype);
            $this->tagStorage->set('frontendmodulecategory', (string) $this->model->frontendmodulecategory);
            $this->tagStorage->set('frontendmoduletemplate', $this->sanitizeInput->getSanitizedFrontendModuleTemplateName((string) $this->model->frontendmoduletype));
            $arrLabel = StringUtil::deserialize($this->model->frontendmoduletrans, true);
            $this->tagStorage->set('frontendmoduletrans_0', $arrLabel[0]);
            $this->tagStorage->set('frontendmoduletrans_1', $arrLabel[1]);
        }

        // Custom route
        if ($this->model->addCustomRoute)
        {
            $this->tagStorage->set('addcustomroute', '1');
        }
        else
        {
            $this->tagStorage->set('addcustomroute', '0');
        }
    }

    /**
     * Add composer.json file to file storage
     *
     * @throws \Exception
     */
    protected function addComposerJsonFileToFileStorage(): void
    {
        $blnModified = false;

        $source = self::SAMPLE_DIR . '/composer.tpl.json';
        $target = sprintf('vendor/%s/%s/composer.json', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add/remove version keyword from composer.json
        $content = $this->fileStorage->getContent();
        $composer = json_decode($content);

        if (isset($composer->version))
        {
            unset($composer->version);
            $blnModified = true;
        }

        if ($this->model->composerpackageversion == '')
        {
            if (isset($composer->version))
            {
                unset($composer->version);
                $blnModified = true;
            }
        }
        else
        {
            $composer->version = $this->model->composerpackageversion;
            $blnModified = true;
        }

        if ($blnModified)
        {
            $content = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $this->fileStorage->replaceContent($content);
        }
    }

    /**
     * Add the bundle class to the file storage
     *
     * @throws \Exception
     */
    protected function addBundleClassToFileStorage(): void
    {
        $source = self::SAMPLE_DIR . '/src/BundleFile.tpl.php';
        $target = sprintf('vendor/%s/%s/src/%s%s.php', $this->model->vendorname, $this->model->repositoryname, $this->sanitizeInput->toPsr4Namespace((string) $this->model->vendorname), $this->sanitizeInput->toPsr4Namespace((string) $this->model->repositoryname));
        $this->fileStorage->createFile($source, $target);
    }

    /**
     * Add the Contao Manager plugin class to the file storage
     *
     * @throws \Exception
     */
    protected function addContaoManagerPluginClassToFileStorage(): void
    {
        $source = self::SAMPLE_DIR . '/src/ContaoManager/Plugin.tpl.php';
        $target = sprintf('vendor/%s/%s/src/ContaoManager/Plugin.php', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);
    }

    /**
     * Add unit tests to the file storage
     *
     * @throws \Exception
     */
    protected function addUnitTestsToFileStorage(): void
    {
        // Add phpunit.xml.dist
        $source = self::SAMPLE_DIR . '/phpunit.xml.tpl.dist';
        $target = sprintf('vendor/%s/%s/phpunit.xml.dist', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add plugin test
        $source = self::SAMPLE_DIR . '/tests/ContaoManager/PluginTest.tpl.php';
        $target = sprintf('vendor/%s/%s/tests/ContaoManager/PluginTest.php', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add .travis.yml
        $source = self::SAMPLE_DIR . '/.travis.tpl.yml';
        $target = sprintf('vendor/%s/%s/.travis.yml', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);
    }

    /**
     * Add custom route to the the file storage
     *
     * @throws \Exception
     */
    protected function addCustomRouteToFileStorage(): void
    {
        // Add controller (custom route)
        $source = self::SAMPLE_DIR . '/src/Controller/MyCustomController.tpl.php';
        $target = sprintf('vendor/%s/%s/src/Controller/MyCustomController.php', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add twig template
        $source = self::SAMPLE_DIR . '/src/Resources/views/my_custom_route.html.tpl.twig';
        $target = sprintf('vendor/%s/%s/src/Resources/views/my_custom_route.html.twig', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);
    }

    /**
     * Add backend module files to the file storage
     *
     * @throws \Exception
     */
    protected function addBackendModuleFilesToFileStorage(): void
    {
        // Add dca table file
        $source = self::SAMPLE_DIR . '/src/Resources/contao/dca/tl_sample_table.tpl.php';
        $target = sprintf('vendor/%s/%s/src/Resources/contao/dca/%s.php', $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable);
        $this->fileStorage->createFile($source, $target);

        // Add dca table translation file
        $source = self::SAMPLE_DIR . '/src/Resources/contao/languages/en/tl_sample_table.tpl.php';
        $target = sprintf('vendor/%s/%s/src/Resources/contao/languages/en/%s.php', $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable);
        $this->fileStorage->createFile($source, $target);

        // Add a sample model
        $source = self::SAMPLE_DIR . '/src/Model/SampleModel.tpl.php';
        $target = sprintf('vendor/%s/%s/src/Model/%s.php', $this->model->vendorname, $this->model->repositoryname, $this->sanitizeInput->getSanitizedModelClassname((string) $this->model->dcatable));
        $this->fileStorage->createFile($source, $target);
    }

    /**
     * Add frontend module files to the file storage
     *
     * @throws \Exception
     */
    protected function addFrontendModuleFilesToFileStorage(): void
    {
        // Get the frontend module template name
        $strFrontenModuleTemplateName = $this->sanitizeInput->getSanitizedFrontendModuleTemplateName((string) $this->model->frontendmoduletype);

        // Get the frontend module classname
        $strFrontendModuleClassname = $this->sanitizeInput->getSanitizedFrontendModuleClassname((string) $this->model->frontendmoduletype);

        // Add frontend module class to src/Controller/FrontendController
        $source = self::SAMPLE_DIR . '/src/Controller/FrontendModule/SampleModule.tpl.php';
        $target = sprintf('vendor/%s/%s/src/Controller/FrontendModule/%s.php', $this->model->vendorname, $this->model->repositoryname, $strFrontendModuleClassname);
        $this->fileStorage->createFile($source, $target);

        // Add frontend module template
        $source = self::SAMPLE_DIR . '/src/Resources/contao/templates/mod_sample.tpl.html5';
        $target = sprintf('vendor/%s/%s/src/Resources/contao/templates/%s.html5', $this->model->vendorname, $this->model->repositoryname, $strFrontenModuleTemplateName);
        $this->fileStorage->createFile($source, $target);
    }

    /**
     * Add miscellaneous files to the file storage
     *
     * @throws \Exception
     */
    protected function addMiscFilesToFileStorage(): void
    {
        // src/Resources/config/*.yml yaml config files
        $arrFiles = ['listener.tpl.yml', 'parameters.tpl.yml', 'services.tpl.yml'];

        if ($this->model->addCustomRoute)
        {
            $arrFiles[] = 'routes.tpl.yml';
        }

        foreach ($arrFiles as $file)
        {
            $source = sprintf('%s/src/Resources/config/%s', self::SAMPLE_DIR, $file);
            $target = sprintf('vendor/%s/%s/src/Resources/config/%s', $this->model->vendorname, $this->model->repositoryname, str_replace('tpl.', '', $file));
            $this->fileStorage->createFile($source, $target)->replaceTags($this->tagStorage);

            // Validate config files
            try
            {
                $arrYaml = Yaml::parse($this->fileStorage->getContent());
                if ($file === 'listener.tpl.yml' || $file === 'services.tpl.yml')
                {
                    if (!array_key_exists('services', $arrYaml))
                    {
                        throw new ParseException('Key "services" not found. Please check the indents.');
                    }
                }

                if ($file === 'parameters.tpl.yml')
                {
                    if (!array_key_exists('parameters', $arrYaml))
                    {
                        throw new ParseException('Key "parameters" not found. Please check the indents.');
                    }
                }
            } catch (ParseException $exception)
            {
                throw new ParseException(sprintf('Unable to parse the YAML string in %s: %s', $target, $exception->getMessage()));
            }
        }

        // src/Resource/contao/config/config.php
        $source = sprintf('%s/src/Resources/contao/config/config.tpl.php', self::SAMPLE_DIR);
        $target = sprintf('vendor/%s/%s/src/Resources/contao/config/config.php', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // src/Resource/contao/languages/en/modules.php
        $source = sprintf('%s/src/Resources/contao/languages/en/modules.tpl.php', self::SAMPLE_DIR);
        $target = sprintf('vendor/%s/%s/src/Resources/contao/languages/en/modules.php', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add logo
        $source = sprintf('%s/src/Resources/public/logo.png', self::SAMPLE_DIR);
        $target = sprintf('vendor/%s/%s/src/Resources/public/logo.png', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Readme.md
        $source = sprintf('%s/README.tpl.md', self::SAMPLE_DIR);
        $target = sprintf('vendor/%s/%s/README.md', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);
    }

    /**
     * Replace php tags and return content from partials
     *
     * @param string $strFilename
     * @return string
     * @throws \Exception
     */
    protected function getContentFromPartialFile(string $strFilename): string
    {
        $sourceFile = self::SAMPLE_DIR . '/partials/' . $strFilename;

        if (!is_file($this->projectDir . '/' . $sourceFile))
        {
            throw new FileNotFoundException(sprintf('Partial file "%s" not found.', $sourceFile));
        }

        $objPartialFile = new File($sourceFile);
        $content = $objPartialFile->getContent();
        $templateParser = new ParsePhpToken($this->tagStorage);
        $content = $templateParser->parsePhpTokens($content);

        return $content;
    }

    /**
     * Zip folder recursively and store it to a predefined destination
     *
     * @param string $source
     * @param string $destination
     * @return bool
     */
    protected function zipData(string $source, string $destination): bool
    {
        if (extension_loaded('zip'))
        {
            $source = $this->projectDir . '/' . $source;
            $destination = $this->projectDir . '/' . $destination;

            if (file_exists($source))
            {
                $zip = new \ZipArchive();
                if ($zip->open($destination, \ZipArchive::CREATE))
                {
                    $source = realpath($source);
                    if (is_dir($source))
                    {
                        $iterator = new \RecursiveDirectoryIterator($source);

                        // Skip dot files while iterating
                        $iterator->setFlags(\RecursiveDirectoryIterator::SKIP_DOTS);
                        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($files as $objSplFileInfo)
                        {
                            $file = $objSplFileInfo->getRealPath();

                            if (is_dir($file))
                            {
                                // Add empty dir and remove the source path
                                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                            }
                            else
                            {
                                if (is_file($file))
                                {
                                    // Add file and remove the source path
                                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                                }
                            }
                        }
                    }
                    else
                    {
                        if (is_file($source))
                        {
                            $zip->addFromString(basename($source), file_get_contents($source));
                        }
                    }
                }
                return $zip->close();
            }
        }
        return false;
    }

    /**
     * Optionally edit the composer.json file located in the root directory
     *
     * @throws \Exception
     */
    protected function editRootComposerJson(): void
    {
        $blnModified = false;
        $objComposerFile = new File('composer.json');
        $content = $objComposerFile->getContent();
        $objJSON = json_decode($content);

        if ($this->model->rootcomposerextendrepositorieskey !== '')
        {
            if (!isset($objJSON->repositories))
            {
                $objJSON->repositories = [];
            }

            $objRepositories = new \stdClass();

            if ($this->model->rootcomposerextendrequirekey === 'path')
            {
                $objRepositories->type = 'path';
                $objRepositories->url = sprintf('%s/vendor/%s/%s', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);

                // Prevent duplicate entries
                if (!\in_array($objRepositories, $objJSON->repositories))
                {
                    $blnModified = true;
                    $objJSON->repositories[] = $objRepositories;
                    $this->message->addInfo('Extended the repositories section in the root composer.json. Please check!');
                }
            }

            if ($this->model->rootcomposerextendrequirekey === 'vcs-github')
            {
                $objRepositories->type = 'vcs';
                $objRepositories->url = sprintf('https://github.com/%s/%s', $this->model->vendorname, $this->model->repositoryname);

                // Prevent duplicate entries
                if (!\in_array($objRepositories, $objJSON->repositories))
                {
                    $blnModified = true;
                    $objJSON->repositories[] = $objRepositories;
                    $this->message->addInfo('Extended the repositories section in the root composer.json. Please check!');
                }
            }
        }

        if ($this->model->rootcomposerextendrequirekey)
        {
            $blnModified = true;
            $objJSON->require->{sprintf('%s/%s', $this->model->vendorname, $this->model->repositoryname)} = 'dev-master';
            $this->message->addInfo('Extended the require section in the root composer.json. Please check!');
        }

        if ($blnModified)
        {
            // Make a backup first
            $strBackupPath = sprintf('system/tmp/composer_backup_%s.json', Date::parse('Y-m-d _H-i-s', time()));
            Files::getInstance()->copy($objComposerFile->path, $strBackupPath);
            $this->message->addInfo(sprintf('Created backup of composer.json in "%s"', $strBackupPath));

            // Append modifications
            $content = json_encode($objJSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $objComposerFile->truncate();
            $objComposerFile->append($content);
            $objComposerFile->close();
        }
    }

    /**
     * Parse templates
     *
     * @throws \Exception
     */
    protected function parseTemplates(): void
    {
        $arrFiles = $this->fileStorage->getAll();

        foreach ($arrFiles as $arrFile)
        {
            $this->fileStorage->getFile($arrFile['target']);

            // Skip images...
            if (isset($arrFile['source']) && !empty($arrFile['source']) && strpos(basename($arrFile['source']), '.tpl.') !== false)
            {
                $this->fileStorage->replaceTags($this->tagStorage);
            }
        }
    }

    /**
     * Write files from the file storage to the filesystem
     *
     * @throws \Exception
     * @todo add a contao hook
     */
    protected function createFilesFromFileStorage(): void
    {
        $arrFiles = $this->fileStorage->getAll();
        $i = 0;

        /**
         * @todo add a contao hook here
         * Manipulate, remove or add files to the storage
         */
        foreach ($arrFiles as $arrFile)
        {
            // Create file
            $objNewFile = new File($arrFile['target']);

            // Overwrite content if file already exists
            $objNewFile->truncate();
            $objNewFile->append($arrFile['content']);
            $objNewFile->close();

            // Display message in the backend
            $this->message->addInfo(sprintf('Created file "%s".', $objNewFile->path));
            $i++;
        }
        // Display message in the backend
        $this->message->addInfo('Added one or more files to the bundle. Please run at least "composer install" or even "composer update", if you have made changes to the root composer.json.');
    }
}
