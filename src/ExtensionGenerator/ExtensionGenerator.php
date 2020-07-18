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

namespace Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator;

use Contao\Controller;
use Contao\Date;
use Contao\File;
use Contao\Files;
use Contao\StringUtil;
use Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Utils\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Utils\Tags;
use Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Message\Message;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ExtensionGenerator
 * @package Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator
 */
class ExtensionGenerator
{
    /** @var SessionInterface */
    protected $session;

    /** @var FileStorage */
    protected $fileStorage;

    /** @var Tags */
    protected $tags;

    /** @var Message */
    protected $message;

    /** @var string */
    protected $projectDir;

    /** @var ContaoBundleCreatorModel */
    protected $model;

    /** @var string */
    const SAMPLE_DIR = 'vendor/markocupic/contao-bundle-creator-bundle/src/Samples/sample-repository';

    /** @var string */
    const STR_INFO_FLASH_TYPE = 'contao.BE.info';

    /** @var string */
    const STR_ERROR_FLASH_TYPE = 'contao.BE.error';

    /**
     * ExtensionGenerator constructor.
     *
     * @param Session $session
     * @param FileStorage $fileStorage
     * @param Tags $tags
     * @param Message $message
     * @param string $projectDir
     */
    public function __construct(Session $session, FileStorage $fileStorage, Tags $tags, Message $message, string $projectDir)
    {
        $this->session = $session;
        $this->fileStorage = $fileStorage;
        $this->tags = $tags;
        $this->message = $message;
        $this->projectDir = $projectDir;
    }

    /**
     * Run bundle creator
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

        // Sanitize model (frontendmoduletype, frontendmodulecategory)
        // Don't move the position it has to be called first!
        $this->sanitizeModel();

        // Set the tags (###****###)
        $this->setTags();

        // Generate the composer.json file
        $this->generateComposerJsonFile();

        // Generate the bundle class
        $this->generateBundleClass();

        // Generate the Contao Manager Plugin class
        $this->generateContaoManagerPluginClass();

        // Config files, assets, etc.
        $this->addMiscFiles();

        // Generate dca table
        if ($this->model->addBackendModule && $this->model->dcatable != '')
        {
            $this->generateBackendModule();
        }

        // Generate frontend module
        if ($this->model->addFrontendModule)
        {
            $this->generateFrontendModule();
        }

        // Create a backup of the old bundle that will be overwritten now
        if ($this->bundleExists())
        {
            $zipSource = sprintf('vendor/%s/%s', $this->model->vendorname, $this->model->repositoryname);
            $zipTarget = sprintf('system/tmp/%s.zip', $this->model->repositoryname . '_backup_' . Date::parse('Y-m-d _H-i-s', time()));
            $this->zipData($zipSource, $zipTarget);
        }

        // Create files from storage in the destination directory vendor/vendorname/bundlename
        $this->createFilesFromStorage();

        // Store new extension for downloading in system/tmp
        $zipSource = sprintf('vendor/%s/%s', $this->model->vendorname, $this->model->repositoryname);
        $zipTarget = sprintf('system/tmp/%s.zip', $this->model->repositoryname);
        if ($this->zipData($zipSource, $zipTarget))
        {
            $this->session->set('CONTAO-BUNDLE-CREATOR-LAST-ZIP', $zipTarget);
        }

        // Optionally extend the composer.json file located in the root directory
        $this->extendRootComposerJson();

    }

    /**
     * Check if extension with same name already exists
     * @return bool
     */
    protected function bundleExists(): bool
    {
        return is_dir($this->projectDir . '/vendor/' . $this->model->vendorname . '/' . $this->model->repositoryname);
    }

    /**
     * Sanitize model
     */
    protected function sanitizeModel(): void
    {

         if ($this->model->backendmoduletype != '')
         {
             // Get the backend module type and sanitize it to the contao backend module convention
             $this->model->backendmoduletype = $this->getSanitizedBackendModuleType();
             $this->model->save();
         }

        if ($this->model->backendmodulecategory != '')
        {
            // Get the backend module category and sanitize it to the contao backend module convention
            $this->model->backendmodulecategory = $this->toSnakecase((string) $this->model->backendmodulecategory);
            $this->model->save();
        }

        if ($this->model->frontendmoduletype != '')
        {
            // Get the frontend module type and sanitize it to the contao frontend module convention
            $this->model->frontendmoduletype = $this->getSanitizedFrontendModuleType();
            $this->model->save();
        }

        if ($this->model->frontendmodulecategory != '')
        {
            // Get the frontend module category and sanitize it to the contao frontend module convention
            $this->model->frontendmodulecategory = $this->toSnakecase((string) $this->model->frontendmodulecategory);
            $this->model->save();
        }
    }

    /**
     * Set all the tags here
     *
     * @todo add a hook
     * @throws \Exception
     */
    protected function setTags(): void
    {
        // Tags
        $this->tags->add('vendorname', (string) $this->model->vendorname);
        $this->tags->add('repositoryname', (string) $this->model->repositoryname);

        // Namespaces
        $this->tags->add('toplevelnamespace', $this->namespaceify((string) $this->model->vendorname));
        $this->tags->add('sublevelnamespace', $this->namespaceify((string) $this->model->repositoryname));

        // Composer
        $this->tags->add('composerdescription', (string) $this->model->composerdescription);
        $this->tags->add('composerlicense', (string) $this->model->composerlicense);
        $this->tags->add('composerauthorname', (string) $this->model->composerauthorname);
        $this->tags->add('composerauthoremail', (string) $this->model->composerauthoremail);
        $this->tags->add('composerauthorwebsite', (string) $this->model->composerauthorwebsite);

        // Phpdoc
        $this->tags->add('bundlename', (string) $this->model->bundlename);
        $this->tags->add('phpdoc', $this->getContentFromPartialFile('phpdoc.txt'));
        $this->tags->add('year', date('Y'));

        // Dca table and backend module
        if ($this->model->addBackendModule && $this->model->dcatable != '')
        {
            $this->tags->add('dcatable', (string) $this->model->dcatable);
            $this->tags->add('backendmoduletype', (string) $this->model->backendmoduletype);
            $this->tags->add('backendmodulecategory', (string) $this->model->backendmodulecategory);
            $arrLabel = StringUtil::deserialize($this->model->backendmoduletrans, true);
            $this->tags->add('backendmoduletrans_0', $arrLabel[0]);
            $this->tags->add('backendmoduletrans_1', $arrLabel[1]);
        }

        // Frontend module
        if ($this->model->addFrontendModule)
        {
            $this->tags->add('frontendmoduleclassname', $this->getSanitizedFrontendModuleClassname());
            $this->tags->add('frontendmoduletype', (string) $this->model->frontendmoduletype);
            $this->tags->add('frontendmodulecategory', (string) $this->model->frontendmodulecategory);
            $this->tags->add('frontendmoduletemplate', $this->getFrontendModuleTemplateName());
            $arrLabel = StringUtil::deserialize($this->model->frontendmoduletrans, true);
            $this->tags->add('frontendmoduletrans_0', $arrLabel[0]);
            $this->tags->add('frontendmoduletrans_1', $arrLabel[1]);
        }
    }

    /**
     * Generate the composer.json file
     *
     * @throws \Exception
     */
    protected function generateComposerJsonFile(): void
    {
        $source = self::SAMPLE_DIR . '/composer.json';
        $target = sprintf('vendor/%s/%s/composer.json', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Add/remove version keyword from composer.json file content
        if ($this->model->composerpackageversion == '')
        {
            $content = preg_replace('/(.*)version(.*)###composerpackageversion###(.*),[\r\n|\n]/', '', $this->fileStorage->getContent());
        }
        else
        {
            $content = preg_replace('/###composerpackageversion###/', $this->model->composerpackageversion, $this->fileStorage->getContent());
        }
        $this->fileStorage->truncate()->appendContent($content);
    }

    /**
     * Generate the bundle class
     *
     * @throws \Exception
     */
    protected function generateBundleClass(): void
    {
        $source = self::SAMPLE_DIR . '/src/BundleFile.php';
        $target = sprintf('vendor/%s/%s/src/%s%s.php', $this->model->vendorname, $this->model->repositoryname, $this->namespaceify((string) $this->model->vendorname), $this->namespaceify((string) $this->model->repositoryname));
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Generate the Contao Manager plugin class
     *
     * @throws \Exception
     */
    protected function generateContaoManagerPluginClass(): void
    {
        $source = self::SAMPLE_DIR . '/src/ContaoManager/Plugin.php';
        $target = sprintf('vendor/%s/%s/src/ContaoManager/Plugin.php', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Generate the dca table and
     * the corresponding language file
     *
     * @throws \Exception
     */
    protected function generateBackendModule(): void
    {
        // Add dca table file
        $source = self::SAMPLE_DIR . '/src/Resources/contao/dca/tl_sample_table.php';
        $target = sprintf('vendor/%s/%s/src/Resources/contao/dca/%s.php', $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable);
        $this->fileStorage->addFile($source, $target);

        // Add dca table translation file
        $source = self::SAMPLE_DIR . '/src/Resources/contao/languages/en/tl_sample_table.php';
        $target = sprintf('vendor/%s/%s/src/Resources/contao/languages/en/%s.php', $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable);
        $this->fileStorage->addFile($source, $target);

        // Append data to src/Resources/contao/config/config.php
        $source = self::SAMPLE_DIR . '/src/Resources/contao/config/config.php';
        $this->fileStorage->getFile($source)->appendContent($this->getContentFromPartialFile('contao_config_be_mod.txt'));

        // Add language array to contao/languages/en/modules.php
        $content = $this->getContentFromPartialFile('contao_lang_en_be_modules.txt');
        $source = self::SAMPLE_DIR . '/src/Resources/contao/languages/en/modules.php';
        $this->fileStorage->getFile($source)->appendContent($content);
    }

    /**
     * Generate frontend module
     *
     * @throws \Exception
     */
    protected function generateFrontendModule(): void
    {
        // Get the frontend module template name
        $strFrontenModuleTemplateName = $this->getFrontendModuleTemplateName();

        // Get the frontend module classname
        $strFrontendModuleClassname = $this->getSanitizedFrontendModuleClassname();

        // Add frontend module class
        $source = self::SAMPLE_DIR . '/src/Controller/FrontendModule/SampleModule.php';
        $target = sprintf('vendor/%s/%s/src/Controller/FrontendModule/%s.php', $this->model->vendorname, $this->model->repositoryname, $strFrontendModuleClassname);
        $this->fileStorage->addFile($source, $target);

        // Add frontend module class to src/Controller/FrontendController
        $source = self::SAMPLE_DIR . '/src/Controller/FrontendModule/SampleModule.php';
        $target = sprintf('vendor/%s/%s/src/Controller/FrontendModule/%s.php', $this->model->vendorname, $this->model->repositoryname, $strFrontendModuleClassname);
        $this->fileStorage->addFile($source, $target);

        // Add src/Resources/contao/dca/tl_module.php
        $source = self::SAMPLE_DIR . '/src/Resources/contao/dca/tl_module.php';
        $target = sprintf('vendor/%s/%s/src/Resources/contao/dca/tl_module.php', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target)->appendContent($this->getContentFromPartialFile('contao_tl_module.txt'));

        // Add frontend module template
        $source = self::SAMPLE_DIR . '/src/Resources/contao/templates/mod_sample.html5';
        $target = sprintf('vendor/%s/%s/src/Resources/contao/templates/%s.html5', $this->model->vendorname, $this->model->repositoryname, $strFrontenModuleTemplateName);
        $this->fileStorage->addFile($source, $target);

        // Add src/Resources/config/services.yml
        $content = $this->getContentFromPartialFile('config_services_frontend_modules.txt');
        $source = self::SAMPLE_DIR . '/src/Resources/config/services.yml';
        $this->fileStorage->getFile($source)->appendContent($content);

        // Add language array to contao/languages/en/modules.php
        $content = $this->getContentFromPartialFile('contao_lang_en_fe_modules.txt');
        $source = self::SAMPLE_DIR . '/src/Resources/contao/languages/en/modules.php';
        $this->fileStorage->getFile($source)->appendContent($content);
    }

    /**
     * Optionally extend the composer.json file located in the root directory
     *
     * @throws \Exception
     */
    protected function extendRootComposerJson(): void
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
            $content = json_encode($objJSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $objComposerFile->truncate();
            $objComposerFile->append($content);
            $objComposerFile->close();
        }
    }

    /**
     * Add miscellaneous files
     *
     * @throws \Exception
     */
    protected function addMiscFiles(): void
    {
        // src/Resources/config/*.yml config files
        $arrFiles = ['listener.yml', 'parameters.yml', 'services.yml'];
        foreach ($arrFiles as $file)
        {
            $source = sprintf('%s/src/Resources/config/%s', self::SAMPLE_DIR, $file);
            $target = sprintf('vendor/%s/%s/src/Resources/config/%s', $this->model->vendorname, $this->model->repositoryname, $file);
            $this->fileStorage->addFile($source, $target);
        }

        // src/Resource/contao/config/config.php
        $source = sprintf('%s/src/Resources/contao/config/config.php', self::SAMPLE_DIR);
        $target = sprintf('vendor/%s/%s/src/Resources/contao/config/config.php', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // src/Resource/contao/languages/en/modules.php
        $source = sprintf('%s/src/Resources/contao/languages/en/modules.php', self::SAMPLE_DIR);
        $target = sprintf('vendor/%s/%s/src/Resources/contao/languages/en/modules.php', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Add logo
        $source = sprintf('%s/src/Resources/public/logo.png', self::SAMPLE_DIR);
        $target = sprintf('vendor/%s/%s/src/Resources/public/logo.png', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Readme.md
        $source = sprintf('%s/README.md', self::SAMPLE_DIR);
        $target = sprintf('vendor/%s/%s/README.md', $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Replace some special tags and return content from partials
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

        /** @var File $objPartialFile */
        $objPartialFile = new File($sourceFile);
        $content = $objPartialFile->getContent();

        // Special treatment for src/Resources/contao/languages/modules.php
        if ($this->model->addBackendModule)
        {
            if (strlen((string) $this->model->backendmodulecategorytrans))
            {
                $content = str_replace('###backendmodulecategorytrans###', $this->model->backendmodulecategorytrans, $content);
                $content = str_replace('###backendmodulecategory###', $this->model->backendmodulecategory, $content);
                $content = preg_replace('/(###modcatstart###|###modcatend###)/', '', $content);
            }
            else
            {
                // Remove obsolete backend module category label
                $content = preg_replace('/([\r\n|\n])###modcatstart###(.*)###modcatend###([\r\n|\n])/', '', $content);
            }
        }

        // Special treatment for src/Resources/contao/languages/modules.php
        if ($this->model->addFrontendModule)
        {
            if (strlen((string) $this->model->frontendmodulecategorytrans))
            {
                $content = str_replace('###frontendmodulecategorytrans###', $this->model->frontendmodulecategorytrans, $content);
                $content = str_replace('###frontendmodulecategory###', $this->model->frontendmodulecategory, $content);
                $content = preg_replace('/(###fmdcatstart###|###fmdcatend###)/', '', $content);
            }
            else
            {
                // Remove obsolete frontend module category label
                $content = preg_replace('/([\r\n|\n])###fmdcatstart###(.*)###fmdcatend###([\r\n|\n])/', '', $content);
            }
        }

        $arrTags = $this->tags->getAll();
        $message = $this->message;
        $newContent = preg_replace_callback('/###([a-zA-Z0-9_\-]{1,})###/', function ($matches) use ($arrTags, $sourceFile, $message) {
            if (!isset($arrTags[$matches[1]]))
            {
                $message->addError(sprintf('Could not replace tag "%s" in "%s", because there is no definition.', $matches[0], $sourceFile));
            }
            return isset($arrTags[$matches[1]]) ? $arrTags[$matches[1]] : $matches[0];
        }, $content);
        return $newContent;
    }

    /**
     * Convert string to namespace
     * "my_custom name-space" will become "MyCustomNameSpace"
     *
     * @param string $strName
     * @return string
     */
    protected function namespaceify(string $strName): string
    {
        $strName = str_replace('_', '-', $strName);
        $strName = str_replace(' ', '-', $strName);
        $arrNamespace = explode('-', $strName);
        $arrNamespace = array_filter($arrNamespace, 'strlen');
        $arrNamespace = array_map('strtolower', $arrNamespace);
        $arrNamespace = array_map('ucfirst', $arrNamespace);
        $strBundleNamespace = implode('', $arrNamespace);

        return $strBundleNamespace;
    }

    /**
     * Converts a string to snakecase
     * My custom module => my_custom_module
     *
     * @param string $str
     * @return string
     */
    protected function toSnakecase(string $str): string
    {
        $str = str_replace(' ', '_', $str);
        $str = str_replace('-', ' ', $str);
        $str = strtolower($str);

        return $str;
    }

    /**
     * Get the frontend module type (f.ex. my_custom_module)
     * Convention => snakecase with postfix "_module"
     *     *
     * @param string $postfix
     * @return string
     */
    protected function getSanitizedFrontendModuleType($postfix = '_module'): string
    {
        $str = $this->toSnakecase((string) $this->model->frontendmoduletype);

        $str = preg_replace('/^(module_|module|mod_|mod)/', '', $str);
        $str = preg_replace('/(_module|module)$/', '', $str);

        // Add postfix
        $str = $str . $postfix;

        return $str;
    }

    /**
     * Get the backend module type (f.ex. my_custom_module)
     * Convention => snakecase
     *     *
     * @return string
     */
    protected function getSanitizedBackendModuleType(): string
    {
        $str = $this->toSnakecase((string) $this->model->backendmoduletype);
        return $str;
    }


    /**
     * Get the frontend module classname from module type and add the "Controller" postfix
     * f.ex. my_custom_module => MyCustomModuleController
     *
     * @param string $postfix
     * @return string
     */
    protected function getSanitizedFrontendModuleClassname(string $postfix = 'Controller'): string
    {
        $str = $this->getSanitizedFrontendModuleType();
        $str = $this->namespaceify($str);
        return $str . $postfix;
    }

    /**
     * Get the frontend module template name from the frontend module type and add the prefix "mod_"
     *
     * @param string $strPrefix
     * @return string
     */
    protected function getFrontendModuleTemplateName($strPrefix = 'mod_'): string
    {
        $str = $this->getSanitizedFrontendModuleType();
        return $strPrefix . $str;
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
     * Write files from storage to the filesystem and replace tags
     *
     * @param bool $blnReplaceTags
     * @throws \Exception
     */
    protected function createFilesFromStorage(bool $blnReplaceTags = true)
    {
        $arrTags = $this->tags->getAll();
        $arrFiles = $this->fileStorage->getAll();
        foreach ($arrFiles as $arrFile)
        {
            if ($blnReplaceTags)
            {
                // Replace tags
                $content = $arrFile['content'];
                $message = $this->message;

                $newContent = preg_replace_callback('/###([a-zA-Z0-9_\-]{1,})###/', function ($matches) use ($arrTags, $arrFile, $message) {
                    if (!isset($arrTags[$matches[1]]))
                    {
                        $message->addError(sprintf('Could not replace tag "%s" in "%s", because there is no definition.', $matches[0], $arrFile['target']));
                    }
                    return isset($arrTags[$matches[1]]) ? $arrTags[$matches[1]] : $matches[0];
                }, $content);
            }
            // Create file
            $objNewFile = new File($arrFile['target']);

            // Overwrite content if file already exists
            $objNewFile->truncate();
            $objNewFile->append($newContent);
            $objNewFile->close();

            // Display message in the backend
            $this->message->addInfo(sprintf('Created file "%s".', $objNewFile->path));
        }
    }
}
