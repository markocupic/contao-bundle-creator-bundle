<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao Bundle Creator
 * @licence    MIT
 * @see        https://github.com/markocupic/contao-bundle-creator
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator;

use Contao\File;
use Contao\Files;
use Contao\Folder;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class ExtensionGenerator
 * @package Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator
 */
class ExtensionGenerator
{
    /** @var SessionInterface */
    private $session;

    /** @var string|string */
    private $projectDir;

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

    /** @var ContaoBundleCreatorModel */
    protected $model;

    /**
     * ExtensionGenerator constructor.
     * @param SessionInterface $session
     * @param string $projectDir
     */
    public function __construct(SessionInterface $session, string $projectDir)
    {
        $this->session = $session;
        $this->projectDir = $projectDir;
    }

    /**
     * @param ContaoBundleCreatorModel $model
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
        $arrFolders[] = sprintf('vendor/%s/%s/src/Resources/contao/languages/de', $this->model->vendorname, $this->model->repositoryname);
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
        $content = str_replace('#licence#', $this->model->licence, $content);
        $content = str_replace('#authorname#', $this->model->authorname, $content);
        $content = str_replace('#authoremail#', $this->model->authoremail, $content);
        $content = str_replace('#authorwebsite#', $this->model->authorwebsite, $content);
        $content = str_replace('#toplevelnamespace#', $this->namespaceify($this->model->vendorname), $content);
        $content = str_replace('#sublevelnamespace#', $this->namespaceify($this->model->repositoryname), $content);

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
     */
    protected function generateBundleClass(): void
    {
        $source = self::SAMPLE_DIR . '/src/BundleFile.php';

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);
        // Top-level namespace
        $content = str_replace('#toplevelnamespace#', $this->namespaceify($this->model->vendorname), $content);
        // Sub-level namespace
        $content = str_replace('#sublevelnamespace#', $this->namespaceify($this->model->repositoryname), $content);

        $target = sprintf('vendor/%s/%s/src/%s%s.php', $this->model->vendorname, $this->model->repositoryname, $this->namespaceify($this->model->vendorname), $this->namespaceify($this->model->repositoryname));

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
     */
    protected function generateContaoManagerPluginClass(): void
    {
        $source = self::SAMPLE_DIR . '/src/ContaoManager/Plugin.php';

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);
        // Top-level namespace
        $content = str_replace('#toplevelnamespace#', $this->namespaceify($this->model->vendorname), $content);
        // Sub-level namespace
        $content = str_replace('#sublevelnamespace#', $this->namespaceify($this->model->repositoryname), $content);

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
     * the corresponding language file
     */
    protected function generateDcaTable(): void
    {
        $arrFiles = [
            // dca table
            self::SAMPLE_DIR . '/src/Resources/contao/dca/tl_sample_table.php'          => sprintf('vendor/%s/%s/src/Resources/contao/dca/%s.php', $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable),
            // lang file
            self::SAMPLE_DIR . '/src/Resources/contao/languages/de/tl_sample_table.php' => sprintf('vendor/%s/%s/src/Resources/contao/languages/de/%s.php', $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable),
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

        // Append backend module string to contao/languages/de/modules.php
        $target = sprintf('vendor/%s/%s/src/Resources/contao/languages/de/modules.php', $this->model->vendorname, $this->model->repositoryname);
        $objFile = new File($target);
        $objFile->append($this->getContentFromPartialFile('contao_lang_de_modules.txt'));
        $objFile->close();
    }

    /**
     * Generate config files
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

        // Contao config/config.php && languages/de/modules.php
        $arrFiles = [
            // Contao config.php
            sprintf('%s/src/Resources/contao/config/config.php', self::SAMPLE_DIR)        => sprintf('vendor/%s/%s/src/Resources/contao/config/config.php', $this->model->vendorname, $this->model->repositoryname),
            // Contao languages/de/modules.php
            sprintf('%s/src/Resources/contao/languages/de/modules.php', self::SAMPLE_DIR) => sprintf('vendor/%s/%s/src/Resources/contao/languages/de/modules.php', $this->model->vendorname, $this->model->repositoryname),

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
        $content = str_replace('#licence#', $this->model->licence, $content);
        $content = str_replace('#authorname#', $this->model->authorname, $content);
        $content = str_replace('#authoremail#', $this->model->authoremail, $content);
        $content = str_replace('#authorwebsite#', $this->model->authorwebsite, $content);
        $content = str_replace('#vendorname#', $this->model->vendorname, $content);
        $content = str_replace('#repositoryname#', $this->model->repositoryname, $content);

        return $content;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getContentFromPartialFile(string $strFilename): string
    {
        $source = self::SAMPLE_DIR . '/partials/' . $strFilename;

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        $content = str_replace('#dcatable#', $this->model->dcatable, $content);
        $content = str_replace('#bemodule#', str_replace('tl_', '', $this->model->dcatable), $content);

        return $content;
    }

    /**
     * Convert string to namespace
     * "my_custom name-space" will become "MyCustomNameSpace"
     *
     * @param string $strName
     * @return string
     */
    private function namespaceify(string $strName): string
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
     * @param string $msg
     */
    private function addInfoFlashMessage(string $msg): void
    {
        $this->addFlashMessage($msg, self::STR_INFO_FLASH_TYPE);
    }

    /**
     * @param string $msg
     */
    private function addErrorFlashMessage(string $msg): void
    {
        $this->addFlashMessage($msg, self::STR_ERROR_FLASH_TYPE);
    }

    /**
     * @param string $msg
     * @param string $type
     */
    private function addFlashMessage(string $msg, string $type): void
    {
        // Get flash bag
        $flashBag = $this->session->getFlashBag();
        $arrFlash = [];
        if ($flashBag->has($type))
        {
            $arrFlash = $flashBag->get($type);
        }

        $arrFlash[] = $msg;

        $flashBag->set($type, $arrFlash);
    }

}
