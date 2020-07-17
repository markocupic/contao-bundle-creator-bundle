<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao Bundle Creator
 * @license    MIT
 * @see        https://github.com/markocupic/contao-bundle-creator
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator;

use Contao\Date;
use Contao\File;
use Contao\Files;
use Contao\Folder;
use Contao\StringUtil;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class ExtensionGenerator
 * @package Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator
 */
class ExtensionGenerator
{
    /** @var Session */
    protected $session;

    /** @var string|string */
    protected $projectDir;

    /** @var ContaoBundleCreatorModel */
    protected $model;

    /** @var string */
    const SAMPLE_DIR = 'vendor/markocupic/contao-bundle-creator-bundle/src/Samples/sample-repository';

    /**
     * @var string
     */
    const STR_INFO_FLASH_TYPE = 'contao.BE.info';

    /**
     * @var string
     */
    const STR_ERROR_FLASH_TYPE = 'contao.BE.error';

    /**
     * ExtensionGenerator constructor.
     * @param Session $session
     * @param string $projectDir
     */
    public function __construct(Session $session, string $projectDir)
    {
        $this->session = $session;
        $this->projectDir = $projectDir;
    }

    /**
     * @param ContaoBundleCreatorModel $model
     * @throws \Exception
     */
    public function run(ContaoBundleCreatorModel $model): void
    {
        $this->model = $model;

        if ($this->bundleExists() && !$this->model->overwriteexisting)
        {
            $this->addErrorFlashMessage('An extension with the same name already exists. Please set the "override extension flag".');
            return;
        }

        $this->addInfoFlashMessage(sprintf('Started generating "%s/%s" bundle.', $this->model->vendorname, $this->model->repositoryname));

        // Generate the folders
        $this->generateFolders();

        // Generate the composer.json file
        $this->generateComposerJsonFile();

        // Generate the bundle class
        $this->generateBundleClass();

        // Generate the Contao Manager Plugin class
        $this->generateContaoManagerPluginClass();

        // Config files, assets, etc.
        $this->copyFiles();

        // Generate dca table
        if ($this->model->addDcaTable && $this->model->dcatable != '')
        {
            $this->generateDcaTable();
        }

        // Generate frontend module
        if ($this->model->addFrontendModule)
        {
            $this->generateFrontendModule();
        }

        $zipSource = sprintf('vendor/%s/%s', $this->model->vendorname, $this->model->repositoryname);
        $zipTarget = sprintf('system/tmp/%s.zip', $this->model->repositoryname);
        if ($this->zipData($zipSource, $zipTarget))
        {
            $this->session->set('CONTAO-BUNDLE-CREATOR-LAST-ZIP', $zipTarget);
        }

        // optionally extend the composer.json file located in the root directory
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
     * Generate the plugiin folder structure
     * @throws \Exception
     */
    protected function generateFolders(): void
    {
        $arrFolders = [];

        $arrFolders[] = sprintf('vendor/%s/%s/src/ContaoManager', $this->model->vendorname, $this->model->repositoryname);
        $arrFolders[] = sprintf('vendor/%s/%s/src/Resources/config', $this->model->vendorname, $this->model->repositoryname);
        $arrFolders[] = sprintf('vendor/%s/%s/src/Resources/public', $this->model->vendorname, $this->model->repositoryname);
        $arrFolders[] = sprintf('vendor/%s/%s/src/Resources/contao/config', $this->model->vendorname, $this->model->repositoryname);
        $arrFolders[] = sprintf('vendor/%s/%s/src/Resources/contao/dca', $this->model->vendorname, $this->model->repositoryname);
        $arrFolders[] = sprintf('vendor/%s/%s/src/Resources/contao/languages/en', $this->model->vendorname, $this->model->repositoryname);
        $arrFolders[] = sprintf('vendor/%s/%s/src/Resources/contao/templates', $this->model->vendorname, $this->model->repositoryname);
        $arrFolders[] = sprintf('vendor/%s/%s/src/EventListener/ContaoHooks', $this->model->vendorname, $this->model->repositoryname);

        foreach ($arrFolders as $strFolder)
        {
            new Folder($strFolder);
        }

        // Add message
        $this->addInfoFlashMessage(sprintf('Generating folder structure in  "vendor/%s/%s".', $this->model->vendorname, $this->model->repositoryname));
    }

    /**
     * Generate the composer.json file
     * @throws \Exception
     */
    protected function generateComposerJsonFile(): void
    {
        $source = self::SAMPLE_DIR . '/composer.json';

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        $content = str_replace('#vendorname#', $this->model->vendorname, $content);
        $content = str_replace('#repositoryname#', $this->model->repositoryname, $content);
        $content = str_replace('#composerdescription#', $this->model->composerdescription, $content);
        $content = str_replace('#composerlicense#', $this->model->composerlicense, $content);
        $content = str_replace('#composerauthorname#', $this->model->composerauthorname, $content);
        $content = str_replace('#composerauthoremail#', $this->model->composerauthoremail, $content);
        $content = str_replace('#composerauthorwebsite#', $this->model->composerauthorwebsite, $content);
        $content = str_replace('#toplevelnamespace#', $this->namespaceify((string) $this->model->vendorname), $content);
        $content = str_replace('#sublevelnamespace#', $this->namespaceify((string) $this->model->repositoryname), $content);
        // Add/remove version keyword
        if ($this->model->composerpackageversion == '')
        {
            $content = preg_replace('/(.*)version(.*)#composerpackageversion#(.*),[\r\n|\n]/', '', $content);
        }
        else
        {
            $content = preg_replace('/#composerpackageversion#/', $this->model->composerpackageversion, $content);
        }

        $target = sprintf('vendor/%s/%s/composer.json', $this->model->vendorname, $this->model->repositoryname);

        /** @var File $objTarget */
        $objTarget = new File($target);
        $objTarget->truncate();
        $objTarget->append($content);
        $objTarget->close();

        // Add message
        $this->addInfoFlashMessage('Generating composer.json file.');
    }

    /**
     * Generate the bundle class
     * @throws \Exception
     */
    protected function generateBundleClass(): void
    {
        $source = self::SAMPLE_DIR . '/src/BundleFile.php';

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);
        // Top-level namespace
        $content = str_replace('#toplevelnamespace#', $this->namespaceify((string) $this->model->vendorname), $content);
        // Sub-level namespace
        $content = str_replace('#sublevelnamespace#', $this->namespaceify((string) $this->model->repositoryname), $content);

        $target = sprintf('vendor/%s/%s/src/%s%s.php', $this->model->vendorname, $this->model->repositoryname, $this->namespaceify((string) $this->model->vendorname), $this->namespaceify((string) $this->model->repositoryname));

        /** @var File $objTarget */
        $objTarget = new File($target);
        $objTarget->truncate();
        $objTarget->append($content);
        $objTarget->close();

        // Add message
        $this->addInfoFlashMessage('Generating bundle class.');
    }

    /**
     * Generate the Contao Manager plugin class
     * @throws \Exception
     */
    protected function generateContaoManagerPluginClass(): void
    {
        $source = self::SAMPLE_DIR . '/src/ContaoManager/Plugin.php';

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);
        // Top-level namespace
        $content = str_replace('#toplevelnamespace#', $this->namespaceify((string) $this->model->vendorname), $content);
        // Sub-level namespace
        $content = str_replace('#sublevelnamespace#', $this->namespaceify((string) $this->model->repositoryname), $content);

        $target = sprintf('vendor/%s/%s/src/ContaoManager/Plugin.php', $this->model->vendorname, $this->model->repositoryname);

        /** @var File $objTarget */
        $objTarget = new File($target);
        $objTarget->truncate();
        $objTarget->append($content);
        $objTarget->close();

        // Add message
        $this->addInfoFlashMessage('Generating Contao Manager Plugin class.');
    }

    /**
     * Generate the dca table and
     * the corresponding language file     * @throws \Exception
     */
    protected function generateDcaTable(): void
    {
        $arrFiles = [
            // dca table
            self::SAMPLE_DIR . '/src/Resources/contao/dca/tl_sample_table.php'          => sprintf('vendor/%s/%s/src/Resources/contao/dca/%s.php', $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable),
            // lang file
            self::SAMPLE_DIR . '/src/Resources/contao/languages/en/tl_sample_table.php' => sprintf('vendor/%s/%s/src/Resources/contao/languages/en/%s.php', $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable),
        ];

        foreach ($arrFiles as $source => $target)
        {
            /** @var File $sourceFile */
            $sourceFile = new File($source);
            $content = $sourceFile->getContent();

            $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);
            $content = str_replace('#dcatable#', $this->model->dcatable, $content);
            /** @var File $objTarget */
            $objTarget = new File($target);
            $objTarget->truncate();
            $objTarget->append($content);
            $objTarget->close();

            // Show message in the backend
            $msg = sprintf('Created file "%s".', $target);
            $this->addInfoFlashMessage($msg);
        }

        // Append backend module string to contao/config.php
        $target = sprintf('vendor/%s/%s/src/Resources/contao/config/config.php', $this->model->vendorname, $this->model->repositoryname);
        $objFile = new File($target);
        $objFile->append($this->getContentFromPartialFile('contao_config_be_mod.txt'));
        $objFile->close();

        // Append backend module string to contao/languages/en/modules.php
        $target = sprintf('vendor/%s/%s/src/Resources/contao/languages/en/modules.php', $this->model->vendorname, $this->model->repositoryname);
        $objFile = new File($target);
        $objFile->append($this->getContentFromPartialFile('contao_lang_en_be_modules.txt'));
        $objFile->close();
    }

    /**
     * Generate frontend module
     * @throws \Exception
     */
    protected function generateFrontendModule(): void
    {
        // Create folders
        $arrFolders = [];
        $arrFolders[] = sprintf('vendor/%s/%s/src/Controller/FrontendModule', $this->model->vendorname, $this->model->repositoryname);
        $arrFolders[] = sprintf('vendor/%s/%s/src/Resources/contao/templates', $this->model->vendorname, $this->model->repositoryname);
        foreach ($arrFolders as $strFolder)
        {
            new Folder($strFolder);
        }

        // Get sample content for the frontend module class
        $objFile = new File(self::SAMPLE_DIR . '/src/Controller/FrontendModule/SampleModule.php');
        $content = $objFile->getContent();

        // Replace #phpdoc# with the phpdoc block in the frontend module class
        $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);

        // Replace #toplevelnamespace# with top-level namespace in the frontend module class
        $content = str_replace('#toplevelnamespace#', $this->namespaceify((string) $this->model->vendorname), $content);

        // Replace #sublevelnamespace# with sub-level namespace in the frontend module class
        $content = str_replace('#sublevelnamespace#', $this->namespaceify((string) $this->model->repositoryname), $content);

        // Get the frontend module type and sanitize it to the contao frontend module convention
        $strFrontendModuleType = $this->getFrontendModuleType();
        $this->model->frontendmoduletype = $strFrontendModuleType;
        $this->model->save();

        // Get the frontend module category and sanitize it to the contao frontend module convention
        $strFrontendModuleCategory = $this->toSnakecase((string) $this->model->frontendmodulecategory);
        $this->model->frontendmodulecategory = $strFrontendModuleCategory;
        $this->model->save();

        // Get the frontend module template name
        $strFrontenModuleTemplateName = $this->getFrontendModuleTemplateName();

        // Replace #frontendmoduleclassname# with frontend module classname
        $strFrontendModuleClassname = $this->getFrontendModuleClassname();
        $content = str_replace('#frontendmoduleclassname#', $strFrontendModuleClassname, $content);

        // Add new frontend class to src/Controller/FrontendController
        $strNewFile = sprintf('vendor/%s/%s/src/Controller/FrontendModule/%s.php', $this->model->vendorname, $this->model->repositoryname, $strFrontendModuleClassname);
        $objNewFile = new File($strNewFile);
        $objNewFile->truncate();
        $objNewFile->append($content);
        $objNewFile->close();

        // Add src/Resources/contao/dca/tl_module.php
        $target = sprintf('vendor/%s/%s/src/Resources/contao/dca/tl_module.php', $this->model->vendorname, $this->model->repositoryname);
        $objNewFile = new File($target);
        $objNewFile->truncate();
        $objSource = new File(self::SAMPLE_DIR . '/src/Resources/contao/dca/tl_module.php');
        $content = $objSource->getContent();

        // Add phpdoc to src/Resources/contao/dca/tl_module.php
        $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);
        $objNewFile->append($content);

        // Add module palette to src/Resources/contao/dca/tl_module.php
        $content = str_replace('#frontendmoduletype#', $strFrontendModuleType, $this->getContentFromPartialFile('contao_tl_module.txt'));
        $objNewFile->append($content);
        $objNewFile->close();

        // Replace tags in src/Resources/config/services.yml
        $target = sprintf('vendor/%s/%s/src/Resources/config/services.yml', $this->model->vendorname, $this->model->repositoryname);
        $objNewFile = new File($target);
        $content = $this->getContentFromPartialFile('config_services_frontend_modules.txt');
        $content = str_replace('#toplevelnamespace#', $this->namespaceify((string) $this->model->vendorname), $content);
        $content = str_replace('#sublevelnamespace#', $this->namespaceify((string) $this->model->repositoryname), $content);
        $content = str_replace('#frontendmoduleclassname#', $strFrontendModuleClassname, $content);
        $content = str_replace('#frontendmodulecategory#', $strFrontendModuleCategory, $content);
        $content = str_replace('#frontendmoduletemplate#', $strFrontenModuleTemplateName, $content);
        $content = str_replace('#frontendmoduletype#', $strFrontendModuleType, $content);

        $objNewFile->append($content);
        $objNewFile->close();

        // Add frontend module template
        $source = self::SAMPLE_DIR . '/src/Resources/contao/templates/mod_sample.html5';
        $target = sprintf('vendor/%s/%s/src/Resources/contao/templates/%s.html5', $this->model->vendorname, $this->model->repositoryname, $strFrontenModuleTemplateName);
        Files::getInstance()->copy($source, $target);

        // Append language array to contao/languages/en/modules.php
        $target = sprintf('vendor/%s/%s/src/Resources/contao/languages/en/modules.php', $this->model->vendorname, $this->model->repositoryname);
        $objFile = new File($target);
        $objFile->append($this->getContentFromPartialFile('contao_lang_en_fe_modules.txt'));
        $objFile->close();

        // Add message in the backend
        $this->addInfoFlashMessage(sprintf('Created frontend module "%s".', $strFrontendModuleClassname));
    }

    /**
     * Optionally extend the composer.json file located in the root directory
     * @throws \Exception
     */
    protected function extendRootComposerJson(): void
    {
        $blnModified = false;
        $objComposerFile = new File('composer.json');
        $content = $objComposerFile->getContent();
        $objJSON = json_decode($content);

        if ($this->model->rootcomposerextendrepositorieskey)
        {
            $blnModified = true;
            if (!isset($objJSON->repositories))
            {
                $objJSON->repositories = [];
            }

            $objRepositories = new \stdClass();
            $objRepositories->type = 'path';
            $objRepositories->url = sprintf('%s/vendor/%s/%s', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);

            // Prevent duplicate entries
            if (!\in_array($objRepositories, $objJSON->repositories))
            {
                $objJSON->repositories[] = $objRepositories;
            }
            $this->addInfoFlashMessage('Extended the repositories section in the root composer.json. Please check!');
        }

        if ($this->model->rootcomposerextendrequirekey)
        {
            $blnModified = true;
            $objJSON->require->{sprintf('%s/%s', $this->model->vendorname, $this->model->repositoryname)} = 'dev-master';
            $this->addInfoFlashMessage('Extended the require section in the root composer.json. Please check!');
        }

        if ($blnModified)
        {
            // Make a backup first
            $strBackupPath = sprintf('system/tmp/composer_backup_%s.json', Date::parse('Y-m-d _H-i-s', time()));
            Files::getInstance()->copy($objComposerFile->path, $strBackupPath);
            $this->addInfoFlashMessage(sprintf('Created backup of composer.json in "%s"', $strBackupPath));

            // Append modifications
            $content = json_encode($objJSON, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $objComposerFile->truncate();
            $objComposerFile->append($content);
            $objComposerFile->close();
        }
    }

    /**
     * Generate config files
     * @throws \Exception
     */
    protected function copyFiles(): void
    {
        // Config files
        $arrFiles = ['listener.yml', 'parameters.yml', 'services.yml'];
        foreach ($arrFiles as $file)
        {
            $source = sprintf('%s/src/Resources/config/%s', self::SAMPLE_DIR, $file);
            $target = sprintf('vendor/%s/%s/src/Resources/config/%s', $this->model->vendorname, $this->model->repositoryname, $file);

            Files::getInstance()->copy($source, $target);

            // Add message
            $this->addInfoFlashMessage(sprintf('Created file "%s".', $target));
        }

        // Contao config/config.php && languages/en/modules.php
        $arrFiles = [
            // Contao config.php
            sprintf('%s/src/Resources/contao/config/config.php', self::SAMPLE_DIR)        => sprintf('vendor/%s/%s/src/Resources/contao/config/config.php', $this->model->vendorname, $this->model->repositoryname),
            // Contao languages/en/modules.php
            sprintf('%s/src/Resources/contao/languages/en/modules.php', self::SAMPLE_DIR) => sprintf('vendor/%s/%s/src/Resources/contao/languages/en/modules.php', $this->model->vendorname, $this->model->repositoryname),

        ];

        foreach ($arrFiles as $source => $target)
        {
            Files::getInstance()->copy($source, $target);

            // Add phpdoc
            $objFile = new File($target);
            $content = $objFile->getContent();
            $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);
            $objFile->truncate();
            $objFile->append($content);
            $objFile->close();

            // Add message
            $this->addInfoFlashMessage(sprintf('Created file "%s".', $target));
        }

        // Assets in src/Resources/public
        $arrFiles = ['logo.png'];
        foreach ($arrFiles as $file)
        {
            $source = sprintf('%s/src/Resources/public/%s', self::SAMPLE_DIR, $file);
            $target = sprintf('vendor/%s/%s/src/Resources/public/%s', $this->model->vendorname, $this->model->repositoryname, $file);

            Files::getInstance()->copy($source, $target);

            // Add message
            $this->addInfoFlashMessage(sprintf('Created file "%s".', $target));
        }

        // README.md
        $arrFiles = ['README.md'];
        foreach ($arrFiles as $file)
        {
            $source = sprintf('%s/%s', self::SAMPLE_DIR, $file);
            $target = sprintf('vendor/%s/%s/%s', $this->model->vendorname, $this->model->repositoryname, $file);

            Files::getInstance()->copy($source, $target);

            // Add message
            $this->addInfoFlashMessage(sprintf('Created file "%s".', $target));
        }
    }

    /**
     * Get the php doc from the partial file
     * @return string
     * @throws \Exception
     */
    protected function getPhpDoc(): string
    {
        $source = self::SAMPLE_DIR . '/partials/phpdoc.txt';

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        $content = str_replace('#bundlename#', $this->model->bundlename, $content);
        $content = str_replace('#year#', date('Y'), $content);
        $content = str_replace('#composerlicense#', $this->model->composerlicense, $content);
        $content = str_replace('#composerauthorname#', $this->model->composerauthorname, $content);
        $content = str_replace('#composerauthoremail#', $this->model->composerauthoremail, $content);
        $content = str_replace('#composerauthorwebsite#', $this->model->composerauthorwebsite, $content);
        $content = str_replace('#vendorname#', $this->model->vendorname, $content);
        $content = str_replace('#repositoryname#', $this->model->repositoryname, $content);

        return $content;
    }

    /**
     * Replace tags and return content from partials
     * @param string $strFilename
     * @return string
     * @throws \Exception
     */
    protected function getContentFromPartialFile(string $strFilename): string
    {
        $source = self::SAMPLE_DIR . '/partials/' . $strFilename;

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        // Handle dca table
        $content = str_replace('#dcatable#', $this->model->dcatable, $content);
        $content = str_replace('#bemodule#', str_replace('tl_', '', $this->model->dcatable), $content);

        // Handle frontend module
        $content = str_replace('#frontendmoduletype#', $this->model->frontendmoduletype, $content);
        $arrLabel = StringUtil::deserialize($this->model->frontendmoduletrans, true);
        $content = str_replace('#frontendmoduletrans_0#', $arrLabel[0], $content);
        $content = str_replace('#frontendmoduletrans_1#', $arrLabel[1], $content);
        if (strlen((string) $this->model->frontendmodulecategorytrans))
        {
            $content = str_replace('#frontendmodulecategorytrans#', $this->model->frontendmodulecategorytrans, $content);
            $content = str_replace('#frontendmodulecategory#', $this->model->frontendmodulecategory, $content);
            $content = preg_replace('/(#fmdcatstart#|#fmdcatend#)/', '', $content);
        }
        else
        {
            // Remove obolete frontend module category label
            $content = preg_replace('/([\r\n|\n])#fmdcatstart#(.*)#fmdcatend#([\r\n|\n])/', '', $content);
        }

        return $content;
    }

    /**
     * @param string $msg
     */
    protected function addInfoFlashMessage(string $msg): void
    {
        $this->addFlashMessage($msg, self::STR_INFO_FLASH_TYPE);
    }

    /**
     * @param string $msg
     */
    protected function addErrorFlashMessage(string $msg): void
    {
        $this->addFlashMessage($msg, self::STR_ERROR_FLASH_TYPE);
    }

    /**
     * @param string $msg
     * @param string $type
     */
    protected function addFlashMessage(string $msg, string $type): void
    {
        /** @var Session $flashBag */
        $flashBag = $this->session->getFlashBag();
        $arrFlash = [];
        if ($flashBag->has($type))
        {
            $arrFlash = $flashBag->get($type);
        }

        $arrFlash[] = $msg;

        $flashBag->set($type, $arrFlash);
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
    protected function getFrontendModuleType($postfix = '_module'): string
    {
        $str = $this->toSnakecase((string) $this->model->frontendmoduletype);

        $str = preg_replace('/^(module_|module|mod_|mod)/', '', $str);
        $str = preg_replace('/(_module|module)$/', '', $str);

        // Add postfix
        $str = $str . $postfix;

        return $str;
    }

    /**
     * Get the frontend module classname from module type and add the "Controller" postfix
     * f.ex. my_custom_module => MyCustomModuleController
     *
     * @param string $postfix
     * @return string
     */
    protected function getFrontendModuleClassname(string $postfix = 'Controller'): string
    {
        $str = $this->getFrontendModuleType();
        $str = $this->namespaceify($str);
        return $str . $postfix;
    }

    /**
     * Get the frontend module template name from the frontend module type and add the prefix "mod_"
     * @param string $strPrefix
     * @return string
     */
    protected function getFrontendModuleTemplateName($strPrefix = 'mod_'): string
    {
        $str = $this->getFrontendModuleType();
        return $strPrefix . $str;
    }

    /**
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
                        // skip dot files while iterating
                        //$iterator->setFlags(\RecursiveDirectoryIterator::SKIP_DOTS);
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
}
