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
use Contao\System;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;

/**
 * Class ExtensionGenerator
 * @package Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator
 */
class ExtensionGenerator
{
    /** @var string */
    const SAMPLE_DIR = 'vendor/markocupic/contao-bundle-creator-bundle/src/Samples/sample-repository';

    /**
     * @var string
     */
    const STR_INFO_FLASH_TYPE = 'contao.BE.info';

    /** @var ContaoBundleCreatorModel */
    protected $model;

    public function __construct()
    {
    }

    /**
     * @param ContaoBundleCreatorModel $model
     */
    public function run(ContaoBundleCreatorModel $model): void
    {
        $this->model = $model;
        $this->generateFolders();

        $this->generateComposerJsonFile();
        $this->generateContaoManagerFile();
        // Config files, assets, etc.
        $this->copyFiles();
        $this->generateBundleFile();

        if ($this->model->dcatable != '')
        {
            $this->generateDcaTable();
        }
    }

    /**
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
    }

    /**
     *
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
        $content = str_replace('#vendornamenamespace#', $this->getNamespaceFromString($this->model->vendorname), $content);
        $content = str_replace('#bundlenamenamespace#', $this->getNamespaceFromString($this->model->repositoryname), $content);

        $target = sprintf('vendor/%s/%s/composer.json', $this->model->vendorname, $this->model->repositoryname);

        /** @var File $objTarget */
        $objTarget = new File($target);
        $objTarget->truncate();
        $objTarget->append($content);
        $objTarget->close();

        // Show message in the backend
        $msg = sprintf('Created file "%s".', $target);
        $this->addInfoFlashMessage($msg);
    }

    /**
     *
     */
    protected function generateBundleFile(): void
    {
        $source = self::SAMPLE_DIR . '/src/BundleFile.php';

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);
        $content = str_replace('#vendornamenamespace#', $this->getNamespaceFromString($this->model->vendorname), $content);
        $content = str_replace('#bundlenamenamespace#', $this->getNamespaceFromString($this->model->repositoryname), $content);

        $target = sprintf('vendor/%s/%s/src/%s%s.php', $this->model->vendorname, $this->model->repositoryname, $this->getNamespaceFromString($this->model->vendorname), $this->getNamespaceFromString($this->model->repositoryname));

        /** @var File $objTarget */
        $objTarget = new File($target);
        $objTarget->truncate();
        $objTarget->append($content);
        $objTarget->close();

        // Show message in the backend
        $msg = sprintf('Created file "%s".', $target);
        $this->addInfoFlashMessage($msg);
    }

    /**
     *
     */
    protected function generateContaoManagerFile(): void
    {
        $source = self::SAMPLE_DIR . '/src/ContaoManager/Plugin.php';

        /** @var File $sourceFile */
        $sourceFile = new File($source);
        $content = $sourceFile->getContent();

        $content = str_replace('#phpdoc#', $this->getPhpDoc(), $content);
        $content = str_replace('#vendornamenamespace#', $this->getNamespaceFromString($this->model->vendorname), $content);
        $content = str_replace('#bundlenamenamespace#', $this->getNamespaceFromString($this->model->repositoryname), $content);

        $target = sprintf('vendor/%s/%s/src/ContaoManager/Plugin.php', $this->model->vendorname, $this->model->repositoryname);

        /** @var File $objTarget */
        $objTarget = new File($target);
        $objTarget->truncate();
        $objTarget->append($content);
        $objTarget->close();

        // Show message in the backend
        $msg = sprintf('Created file "%s".', $target);
        $this->addInfoFlashMessage($msg);
    }

    /**
     * Generate dca table and the corresponding language file
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
        $objFile->append($this->getTemplateFile('contao_config_be_mod.txt'));
        $objFile->close();

        // Append backend module string to contao/languages/de/modules.php
        $target = sprintf('vendor/%s/%s/src/Resources/contao/languages/de/modules.php', $this->model->vendorname, $this->model->repositoryname);
        $objFile = new File($target);
        $objFile->append($this->getTemplateFile('contao_lang_de_modules.txt'));
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
        }

        // Assets
        $arrFiles = ['logo.png'];
        foreach ($arrFiles as $file)
        {
            $source = sprintf('%s/src/Resources/public/%s', self::SAMPLE_DIR, $file);
            $target = sprintf('vendor/%s/%s/src/Resources/public/%s', $this->model->vendorname, $this->model->repositoryname, $file);

            Files::getInstance()->copy($source, $target);
        }

        // Readme
        $arrFiles = ['README.md'];
        foreach ($arrFiles as $file)
        {
            $source = sprintf('%s/%s', self::SAMPLE_DIR, $file);
            $target = sprintf('vendor/%s/%s/%s', $this->model->vendorname, $this->model->repositoryname, $file);

            Files::getInstance()->copy($source, $target);
        }
    }

    /**
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
    protected function getTemplateFile(string $strFilename): string
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
     * @param string $strName
     * @return string
     */
    private function getNamespaceFromString(string $strName): string
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
        // Get flash bag
        $session = System::getContainer()->get('session');
        $flashBag = $session->getFlashBag();
        $arrFlash = [];
        if ($flashBag->has(static::STR_INFO_FLASH_TYPE))
        {
            $arrFlash = $flashBag->get(static::STR_INFO_FLASH_TYPE);
        }

        $arrFlash[] = $msg;

        $flashBag->set(static::STR_INFO_FLASH_TYPE, $arrFlash);
    }

}
