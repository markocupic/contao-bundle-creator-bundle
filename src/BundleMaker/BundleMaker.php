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
use Markocupic\ZipBundle\Zip\Zip;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BundleMaker
 *
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker
 */
class BundleMaker
{
    /** @var string */
    const SAMPLE_DIR = 'vendor/markocupic/contao-bundle-creator-bundle/src/Resources/skeleton/sample-repository';

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
            $zipSource = sprintf('%s/vendor/%s/%s', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
            $zipTarget = sprintf('%s/system/tmp/%s.zip', $this->projectDir, $this->model->repositoryname . '_backup_' . Date::parse('Y-m-d _H-i-s', time()));
            (new Zip())
                ->stripSourcePath(true)
                ->saveAsFile(basename($zipTarget))
                ->zipDirRecursive($zipSource, dirname($zipTarget));
        }

        // Replace if-tokens and replace simple tokens in file storage
        $this->parseTemplates();

        // Create all the bundle files in vendor/vendorname/bundlename
        $this->createBundleFiles();

        // Store new bundle also as a zip-package for downloading it from system/tmp
        $zipSource = sprintf('%s/vendor/%s/%s', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $zipTarget = sprintf('%s/system/tmp/%s.zip', $this->projectDir, $this->model->repositoryname);
        $zip = (new Zip())
            ->stripSourcePath(true)
            ->saveAsFile(basename($zipTarget));
        if ($zip->zipDirRecursive($zipSource, dirname($zipTarget)))
        {
            $this->session->set('CONTAO-BUNDLE-CREATOR.LAST-ZIP', str_replace($this->projectDir . '/', '', $zipTarget));
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
     * Add composer.json file to file storage
     *
     * @throws \Exception
     */
    protected function addComposerJsonFileToFileStorage(): void
    {
        $blnModified = false;

        $source = sprintf('%s/%s/composer.tpl.json', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/composer.json', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
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
        $source = sprintf('%s/%s/src/BundleFile.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/%s%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $this->sanitizeInput->toPsr4Namespace((string) $this->model->vendorname), $this->sanitizeInput->toPsr4Namespace((string) $this->model->repositoryname));
        $this->fileStorage->createFile($source, $target);
    }

    /**
     * Add the Contao Manager plugin class to the file storage
     *
     * @throws \Exception
     */
    protected function addContaoManagerPluginClassToFileStorage(): void
    {
        $source = sprintf('%s/%s/src/ContaoManager/Plugin.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/ContaoManager/Plugin.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
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
        $source = sprintf('%s/%s/phpunit.xml.tpl.dist', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/phpunit.xml.dist', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add plugin test
        $source = sprintf('%s/%s/tests/ContaoManager/PluginTest.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/tests/ContaoManager/PluginTest.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add .travis.yml
        $source = sprintf('%s/%s/.travis.tpl.yml', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.travis.yml', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add .php_cs.dist
        $source = sprintf('%s/%s/.php_cs.tpl.dist', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.php_cs.dist', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
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
            $source = sprintf('%s/%s/src/Resources/config/%s', $this->projectDir, self::SAMPLE_DIR, $file);
            $target = sprintf('%s/vendor/%s/%s/src/Resources/config/%s', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, str_replace('tpl.', '', $file));
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
        $source = sprintf('%s/%s/src/Resources/contao/config/config.tpl.php', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/config/config.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add logo
        $source = sprintf('%s/%s/src/Resources/public/logo.png', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/public/logo.png', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Readme.md
        $source = sprintf('%s/%s/README.tpl.md', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/README.md', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
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
        $source = sprintf('%s/%s/src/Resources/contao/dca/tl_sample_table.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/dca/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable);
        $this->fileStorage->createFile($source, $target);

        // Add dca table translation file
        $source = sprintf('%s/%s/src/Resources/contao/languages/en/tl_sample_table.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable);
        $this->fileStorage->createFile($source, $target);

        // Add a sample model
        $source = sprintf('%s/%s/src/Model/SampleModel.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Model/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $this->sanitizeInput->getSanitizedModelClassname((string) $this->model->dcatable));
        $this->fileStorage->createFile($source, $target);

        // Add src/Resources/contao/languages/en/modules.php to file storage
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/modules.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        if (!$this->fileStorage->hasFile($target))
        {
            $source = sprintf('%s/%s/src/Resources/contao/languages/en/modules.tpl.php', $this->projectDir, static::SAMPLE_DIR);
            $this->fileStorage->createFile($source, $target);
        }

        // Add src/Resources/contao/languages/en/default.php to file storage
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/default.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        if (!$this->fileStorage->hasFile($target))
        {
            $source = sprintf('%s/%s/src/Resources/contao/languages/en/default.tpl.php', $this->projectDir, static::SAMPLE_DIR);
            $this->fileStorage->createFile($source, $target);
        }
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
        $source = sprintf('%s/%s/src/Controller/FrontendModule/SampleModule.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Controller/FrontendModule/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $strFrontendModuleClassname);
        $this->fileStorage->createFile($source, $target);

        // Add frontend module template
        $source = sprintf('%s/%s/src/Resources/contao/templates/mod_sample.tpl.html5', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/templates/%s.html5', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $strFrontenModuleTemplateName);
        $this->fileStorage->createFile($source, $target);

        // Add src/Resources/contao/dca/tl_module.php
        $source = sprintf('%s/%s/src/Resources/contao/dca/tl_module.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/dca/tl_module.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add src/Resources/contao/languages/en/modules.php to file storage
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/modules.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        if (!$this->fileStorage->hasFile($target))
        {
            $source = sprintf('%s/%s/src/Resources/contao/languages/en/modules.tpl.php', $this->projectDir, static::SAMPLE_DIR);
            $this->fileStorage->createFile($source, $target);
        }

        // Add src/Resources/contao/languages/en/default.php to file storage
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/default.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        if (!$this->fileStorage->hasFile($target))
        {
            $source = sprintf('%s/%s/src/Resources/contao/languages/en/default.tpl.php', $this->projectDir, static::SAMPLE_DIR);
            $this->fileStorage->createFile($source, $target);
        }
    }

    /**
     * Add custom route to the the file storage
     *
     * @throws \Exception
     */
    protected function addCustomRouteToFileStorage(): void
    {
        // Add controller (custom route)
        $source = sprintf('%s/%s/src/Controller/MyCustomController.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Controller/MyCustomController.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);

        // Add twig template
        $source = sprintf('%s/%s/src/Resources/views/my_custom_route.html.tpl.twig', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/views/my_custom_route.html.twig', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->createFile($source, $target);
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
    protected function createBundleFiles(): void
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
            $target = str_replace($this->projectDir . '/', '', $arrFile['target']);
            $objNewFile = new File($target);

            // Overwrite file content if file already exists
            $objNewFile->write($arrFile['content']);
            $objNewFile->close();

            // Display message in the backend
            $this->message->addInfo(sprintf('Created file "%s".', $objNewFile->path));
            $i++;
        }
        // Display message in the backend
        $this->message->addInfo('Added one or more files to the bundle. Please run at least "composer install" or even "composer update", if you have made changes to the root composer.json.');
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
                $objRepositories->url = sprintf('vendor/%s/%s', $this->model->vendorname, $this->model->repositoryname);

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
}
