<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker;

use Contao\Date;
use Contao\StringUtil;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken\ParsePhpToken;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Markocupic\ZipBundle\Zip\Zip;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BundleMaker.
 */
class BundleMaker
{
    /**
     * @var string
     */
    const SAMPLE_DIR = 'vendor/markocupic/contao-bundle-creator-bundle/src/Resources/skeleton';

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
    protected $model;

    /**
     * BundleMaker constructor.
     */
    public function __construct(Session $session, FileStorage $fileStorage, TagStorage $tagStorage, Message $message, Zip $zip, string $projectDir)
    {
        $this->session = $session;
        $this->fileStorage = $fileStorage;
        $this->tagStorage = $tagStorage;
        $this->message = $message;
        $this->zip = $zip;
        $this->projectDir = $projectDir;
    }

    /**
     * Run contao bundle creator.
     *
     * @throws \Exception
     */
    public function run(ContaoBundleCreatorModel $model): void
    {
        $this->model = $model;

        if ($this->bundleExists() && !$this->model->overwriteexisting) {
            $this->message->addError('An extension with the same name already exists. Please set the "override extension flag".');

            return;
        }

        $this->message->addInfo(sprintf('Started generating "%s/%s" bundle.', $this->model->vendorname, $this->model->repositoryname));

        // Set the php template tags
        $this->setTags();

        // Add the composer.json file to file storage
        $this->addComposerJsonFileToFileStorage();

        // Add the bundle class to file storage
        $this->addBundleClassToFileStorage();

        // Add the Dependency Injection Extension class to file storage
        $this->addDependencyInjectionExtensionClassToFileStorage();

        // Add the Contao Manager Plugin class to file storage
        $this->addContaoManagerPluginClassToFileStorage();

        // Add unit tests to file storage
        $this->addContinuousIntegrationToFileStorage();

        // Config files, assets, etc.
        $this->addMiscFilesToFileStorage();

        // Add ecs config files to the bundle
        if ($this->model->addEasyCodingStandard) {
            $this->addEasyCodingStandard();
        }

        // Add backend module files to file storage
        if ($this->model->addBackendModule && '' !== $this->model->dcatable) {
            $this->addBackendModuleFilesToFileStorage();
        }

        // Add frontend module files to file storage
        if ($this->model->addFrontendModule) {
            $this->addFrontendModuleFilesToFileStorage();
        }

        // Add content element files to file storage
        if ($this->model->addContentElement) {
            $this->addContentElementFilesToFileStorage();
        }

        // Add a custom route to the file storage
        if ($this->model->addCustomRoute) {
            $this->addCustomRouteToFileStorage();
        }

        // Create a backup of the old bundle that will be overwritten now
        if ($this->bundleExists()) {
            $zipSource = sprintf('%s/vendor/%s/%s', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
            $zipTarget = sprintf('%s/system/tmp/%s.zip', $this->projectDir, $this->model->repositoryname.'_backup_'.Date::parse('Y-m-d _H-i-s', time()));
            $this->zip
                ->stripSourcePath($zipSource)
                ->addDirRecursive($zipSource)
                ->run($zipTarget)
            ;
        }

        // Replace php tokens in file storage
        $this->parseTemplates();

        // Copy all the bundle files from the storage to the destination directories in vendor/vendorname/bundlename
        $this->createBundleFiles();

        // Store new bundle also as a zip-package in system/tmp for downloading it after the generating process
        $zipSource = sprintf('%s/vendor/%s/%s', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $zipTarget = sprintf('%s/system/tmp/%s.zip', $this->projectDir, $this->model->repositoryname);
        $zip = $this->zip
            ->stripSourcePath($zipSource)
            ->addDirRecursive($zipSource)
        ;

        if ($zip->run($zipTarget)) {
            $this->session->set('CONTAO-BUNDLE-CREATOR.LAST-ZIP', str_replace($this->projectDir.'/', '', $zipTarget));
        }

        // Optionally extend the composer.json file located in the root directory
        $this->editRootComposerJson();
    }

    /**
     * Check if an extension with the same name already exists.
     */
    protected function bundleExists(): bool
    {
        return is_dir($this->projectDir.'/vendor/'.$this->model->vendorname.'/'.$this->model->repositoryname);
    }

    /**
     * Set all the tags here.
     *
     * @throws \Exception
     *
     * @todo add a contao hook
     */
    protected function setTags(): void
    {
        // Store model values into the tag storage
        $arrModel = $this->model->row();

        foreach ($arrModel as $fieldname => $value) {
            $this->tagStorage->set((string) $fieldname, (string) $value);
        }

        // Tags
        $this->tagStorage->set('vendorname', (string) $this->model->vendorname);
        $this->tagStorage->set('repositoryname', (string) $this->model->repositoryname);
        $this->tagStorage->set('dependencyinjectionextensionclassname', Str::asDependencyInjectionExtensionClassName((string) $this->model->vendorname, (string) $this->model->repositoryname));

        // Namespaces
        $this->tagStorage->set('toplevelnamespace', Str::asClassName((string) $this->model->vendorname));
        $this->tagStorage->set('sublevelnamespace', Str::asClassName((string) $this->model->repositoryname));

        // Twig namespace @Vendor/Bundlename
        $this->tagStorage->set('twignamespace', Str::asTwigNameSpace((string) $this->model->vendorname, (string) $this->model->repositoryname));

        // Composer
        $this->tagStorage->set('composerdescription', (string) $this->model->composerdescription);
        $this->tagStorage->set('composerlicense', (string) $this->model->composerlicense);
        $this->tagStorage->set('composerauthorname', (string) $this->model->composerauthorname);
        $this->tagStorage->set('composerauthoremail', (string) $this->model->composerauthoremail);
        $this->tagStorage->set('composerauthorwebsite', (string) $this->model->composerauthorwebsite);

        // Phpdoc
        $this->tagStorage->set('bundlename', (string) $this->model->bundlename);
        $this->tagStorage->set('phpdoc', Str::generateHeaderCommentFromString($this->getContentFromPartialFile('phpdoc.tpl.txt')));
        $phpdoclines = explode(PHP_EOL, $this->getContentFromPartialFile('phpdoc.tpl.txt'));
        $ecsphpdoc = preg_replace("/[\r\n|\n]+/", '', implode('', array_map(static function ($line) {return $line.'\n'; }, $phpdoclines)));
        $this->tagStorage->set('ecsphpdoc', $ecsphpdoc);

        // Current year
        $this->tagStorage->set('year', date('Y'));

        // Dca table and backend module
        if ($this->model->addBackendModule && '' !== $this->model->dcatable) {
            $this->tagStorage->set('dcatable', (string) $this->model->dcatable);
            $this->tagStorage->set('modelclassname', (string) Str::asContaoModelClassName((string) $this->model->dcatable));
            $this->tagStorage->set('backendmoduletype', (string) $this->model->backendmoduletype);
            $this->tagStorage->set('backendmodulecategory', (string) $this->model->backendmodulecategory);
            $arrLabel = StringUtil::deserialize($this->model->backendmoduletrans, true);
            $this->tagStorage->set('backendmoduletrans_0', $arrLabel[0]);
            $this->tagStorage->set('backendmoduletrans_1', $arrLabel[1]);
        }

        // Frontend module
        if ($this->model->addFrontendModule) {
            $this->tagStorage->set('frontendmoduleclassname', Str::asContaoFrontendModuleClassName((string) $this->model->frontendmoduletype));
            $this->tagStorage->set('frontendmoduletype', (string) $this->model->frontendmoduletype);
            $this->tagStorage->set('frontendmodulecategory', (string) $this->model->frontendmodulecategory);
            $this->tagStorage->set('frontendmoduletemplate', Str::asContaoFrontendModuleTemplateName((string) $this->model->frontendmoduletype));
            $arrLabel = StringUtil::deserialize($this->model->frontendmoduletrans, true);
            $this->tagStorage->set('frontendmoduletrans_0', $arrLabel[0]);
            $this->tagStorage->set('frontendmoduletrans_1', $arrLabel[1]);
        }

        // Content element
        if ($this->model->addContentElement) {
            $this->tagStorage->set('contentelementclassname', Str::asContaoContentElementClassName((string) $this->model->contentelementtype));
            $this->tagStorage->set('contentelementtype', (string) $this->model->contentelementtype);
            $this->tagStorage->set('contentelementcategory', (string) $this->model->contentelementcategory);
            $this->tagStorage->set('contentelementtemplate', Str::asContaoContentElementTemplateName((string) $this->model->contentelementtype));
            $arrLabel = StringUtil::deserialize($this->model->contentelementtrans, true);
            $this->tagStorage->set('contentelementtrans_0', $arrLabel[0]);
            $this->tagStorage->set('contentelementtrans_1', $arrLabel[1]);
        }

        // Custom route
        $subject = sprintf(
            '%s_%s',
            strtolower($this->model->vendorname),
            strtolower($this->model->repositoryname)
        );
        $subject = preg_replace('/-bundle$/', '', $subject);
        $routeId = preg_replace('/-/', '_', $subject);
        $this->tagStorage->set('routeid', $routeId);

        if ($this->model->addCustomRoute) {
            $this->tagStorage->set('addCustomRoute', '1');
        } else {
            $this->tagStorage->set('addCustomRoute', '0');
        }
    }

    /**
     * Replace php tags and return content from partials.
     *
     * @throws \Exception
     */
    protected function getContentFromPartialFile(string $strFilename): string
    {
        $sourceFile = self::SAMPLE_DIR.'/partials/'.$strFilename;

        if (!is_file($this->projectDir.'/'.$sourceFile)) {
            throw new FileNotFoundException(sprintf('Partial file "%s" not found.', $sourceFile));
        }

        $content = file_get_contents($this->projectDir.'/'.$sourceFile);
        $templateParser = new ParsePhpToken($this->tagStorage);

        return $templateParser->parsePhpTokensFromString($content);
    }

    /**
     * Add composer.json file to file storage.
     *
     * @throws \Exception
     */
    protected function addComposerJsonFileToFileStorage(): void
    {
        $objComposer = new \stdClass();

        // Name
        $objComposer->name = $this->tagStorage->get('vendorname').'/'.$this->tagStorage->get('repositoryname');

        // Description
        $objComposer->description = $this->tagStorage->get('composerdescription');

        // Keywords
        $objComposer->keywords = [
            'contao',
            'bundle',
            'tag 3',
            'tag 4',
        ];

        // Type
        $objComposer->type = 'contao-bundle';

        // License
        $objComposer->license = $this->tagStorage->get('composerlicense');

        //Authors
        $objComposer->authors = [];
        $authors = new \stdClass();
        $authors->name = $this->tagStorage->get('composerauthorname');
        $authors->email = $this->tagStorage->get('composerauthoremail');
        $authors->homepage = $this->tagStorage->get('composerauthorwebsite');
        $authors->role = 'Developer';
        $objComposer->authors[] = $authors;

        // Support
        $objComposer->support = new \stdClass();
        $objComposer->support->issues = sprintf(
            'https://github.com/%s/%s/issues',
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname')
            );
        $objComposer->support->source = sprintf(
            'https://github.com/%s/%s',
            $this->tagStorage->get('vendorname'),
            $this->tagStorage->get('repositoryname'),
        );

        // Version
        if (!empty($this->model->composerpackageversion)) {
            $objComposer->version = $this->model->composerpackageversion;
        }

        // Require
        $objComposer->require = new \stdClass();
        $objComposer->require->{'contao/core-bundle'} = '^4.9';

        // Require-dev
        $objComposer->{'require-dev'} = new \stdClass();
        $objComposer->{'require-dev'}->{'contao/test-case'} = '^4.0';
        $objComposer->{'require-dev'}->{'contao/manager-plugin'} = '^2.3';
        $objComposer->{'require-dev'}->{'phpunit/phpunit'} = '^8.4';
        $objComposer->{'require-dev'}->{'symfony/phpunit-bridge'} = '4.4.*';

        if ($this->model->addEasyCodingStandard) {
            $objComposer->{'require-dev'}->{'contao/easy-coding-standard'} = '^2.1';
        }

        // Autoload
        $objComposer->autoload = new \stdClass();
        $objComposer->autoload->{'psr-4'} = new \stdClass();
        $objComposer->autoload->{'psr-4'}->{sprintf('%s\\%s\\', $this->tagStorage->get('toplevelnamespace'), $this->tagStorage->get('sublevelnamespace'))} = 'src/';
        $objComposer->autoload->{'classmap'} = ['src/Resources/contao'];
        $objComposer->autoload->{'exclude-from-classmap'} = [
            'src/Resources/contao/config',
            'src/Resources/contao/dca',
            'src/Resources/contao/languages',
            'src/Resources/contao/templates',
        ];

        // Extra
        $objComposer->extra = new \stdClass();
        $objComposer->extra->{'contao-manager-plugin'} = sprintf(
            '%s\%s\ContaoManager\Plugin',
            $this->tagStorage->get('toplevelnamespace'),
            $this->tagStorage->get('sublevelnamespace')
            );

        $content = json_encode($objComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $target = sprintf('%s/vendor/%s/%s/composer.json', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFileFromString($target, $content);
    }

    /**
     * Add the bundle class to the file storage.
     *
     * @throws \Exception
     */
    protected function addBundleClassToFileStorage(): void
    {
        $source = sprintf('%s/%s/src/Class.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/%s%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, Str::asClassName((string) $this->model->vendorname), Str::asClassName((string) $this->model->repositoryname));
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Add the Dependency Injection Extension class to the file storage.
     *
     * @throws \Exception
     */
    protected function addDependencyInjectionExtensionClassToFileStorage(): void
    {
        $source = sprintf('%s/%s/src/DependencyInjection/Extension.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/DependencyInjection/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, Str::asDependencyInjectionExtensionClassName((string) $this->model->vendorname, (string) $this->model->repositoryname));
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Add the Contao Manager plugin class to the file storage.
     *
     * @throws \Exception
     */
    protected function addContaoManagerPluginClassToFileStorage(): void
    {
        $source = sprintf('%s/%s/src/ContaoManager/Plugin.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/ContaoManager/Plugin.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Add continuous integration to the file storage (phpunit tests, travis, github workflows).
     *
     * @throws \Exception
     */
    protected function addContinuousIntegrationToFileStorage(): void
    {
        // Add phpunit.xml.dist
        $source = sprintf('%s/%s/phpunit.xml.tpl.dist', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/phpunit.xml.dist', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Add plugin test
        $source = sprintf('%s/%s/tests/ContaoManager/PluginTest.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/tests/ContaoManager/PluginTest.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Add .travis.yml
        $source = sprintf('%s/%s/.travis.tpl.yml', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.travis.yml', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Add github workflow file
        $source = sprintf('%s/%s/.github/workflows/ci.tpl.yml', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.github/workflows/ci.yml', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Add miscellaneous files to the file storage.
     *
     * @throws \Exception
     */
    protected function addMiscFilesToFileStorage(): void
    {
        // src/Resources/config/*.yml yaml config files
        $arrFiles = ['listener.tpl.yml', 'parameters.tpl.yml', 'services.tpl.yml'];

        if ($this->model->addCustomRoute) {
            $arrFiles[] = 'routes.tpl.yml';
        }

        foreach ($arrFiles as $file) {
            $source = sprintf('%s/%s/src/Resources/config/%s', $this->projectDir, self::SAMPLE_DIR, $file);
            $target = sprintf('%s/vendor/%s/%s/src/Resources/config/%s', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, str_replace('tpl.', '', $file));
            $this->fileStorage->addFile($source, $target)->replaceTags($this->tagStorage);

            // Validate config files
            try {
                $arrYaml = Yaml::parse($this->fileStorage->getContent());

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

        // src/Resource/contao/config/config.php
        $source = sprintf('%s/%s/src/Resources/contao/config/config.tpl.php', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/config/config.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Add logo
        $source = sprintf('%s/%s/src/Resources/public/logo.png', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/public/logo.png', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Readme.md
        $source = sprintf('%s/%s/README.tpl.md', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/README.md', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // .gitattributes
        $source = sprintf('%s/%s/.gitattributes.tpl.txt', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.gitattributes', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Add easy coding standard files to the file storage.
     *
     * @throws \Exception
     */
    protected function addEasyCodingStandard(): void
    {
        // .ecs/config/set/contao.yaml
        $source = sprintf('%s/%s/.ecs/config/set/contao.tpl.yaml', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.ecs/config/set/contao.yaml', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // .ecs/config/set/header_comment_fixer.yaml
        $source = sprintf('%s/%s/.ecs/config/set/header_comment_fixer.tpl.yaml', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.ecs/config/set/header_comment_fixer.yaml', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // .ecs/config/default.yaml
        $source = sprintf('%s/%s/.ecs/config/default.tpl.yaml', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.ecs/config/default.yaml', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // .ecs/config/legacy.yaml
        $source = sprintf('%s/%s/.ecs/config/legacy.tpl.yaml', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.ecs/config/legacy.yaml', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // .ecs/config/self.yaml
        $source = sprintf('%s/%s/.ecs/config/self.tpl.yaml', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.ecs/config/self.yaml', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // .ecs/config/template.yaml
        $source = sprintf('%s/%s/.ecs/config/template.tpl.yaml', $this->projectDir, self::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/.ecs/config/template.yaml', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Add backend module files to the file storage.
     *
     * @throws \Exception
     */
    protected function addBackendModuleFilesToFileStorage(): void
    {
        // Add dca table file
        $source = sprintf('%s/%s/src/Resources/contao/dca/tl_sample_table.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/dca/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable);
        $this->fileStorage->addFile($source, $target);

        // Add dca table translation file
        $source = sprintf('%s/%s/src/Resources/contao/languages/en/tl_sample_table.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $this->model->dcatable);
        $this->fileStorage->addFile($source, $target);

        // Add a sample model
        $source = sprintf('%s/%s/src/Model/Model.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Model/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, Str::asContaoModelClassName((string) $this->model->dcatable));
        $this->fileStorage->addFile($source, $target);

        // Add src/Resources/contao/languages/en/modules.php to file storage
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/modules.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);

        if (!$this->fileStorage->hasFile($target)) {
            $source = sprintf('%s/%s/src/Resources/contao/languages/en/modules.tpl.php', $this->projectDir, static::SAMPLE_DIR);
            $this->fileStorage->addFile($source, $target);
        }

        // Add src/Resources/contao/languages/en/default.php to file storage
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/default.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);

        if (!$this->fileStorage->hasFile($target)) {
            $source = sprintf('%s/%s/src/Resources/contao/languages/en/default.tpl.php', $this->projectDir, static::SAMPLE_DIR);
            $this->fileStorage->addFile($source, $target);
        }
    }

    /**
     * Add frontend module files to the file storage.
     *
     * @throws \Exception
     */
    protected function addFrontendModuleFilesToFileStorage(): void
    {
        // Get the frontend module template name
        $strFrontenModuleTemplateName = Str::asContaoFrontendModuleTemplateName((string) $this->model->frontendmoduletype);

        // Get the frontend module classname
        $strFrontendModuleClassname = Str::asContaoFrontendModuleClassName((string) $this->model->frontendmoduletype);

        // Add frontend module class to src/Controller/FrontendModuleController
        $source = sprintf('%s/%s/src/Controller/FrontendModule/FrontendModuleController.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Controller/FrontendModule/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $strFrontendModuleClassname);
        $this->fileStorage->addFile($source, $target);

        // Add frontend module template
        $source = sprintf('%s/%s/src/Resources/contao/templates/mod_sample_module.tpl.html5', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/templates/%s.html5', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $strFrontenModuleTemplateName);
        $this->fileStorage->addFile($source, $target);

        // Add src/Resources/contao/dca/tl_module.php
        $source = sprintf('%s/%s/src/Resources/contao/dca/tl_module.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/dca/tl_module.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Add src/Resources/contao/languages/en/modules.php to file storage
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/modules.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);

        if (!$this->fileStorage->hasFile($target)) {
            $source = sprintf('%s/%s/src/Resources/contao/languages/en/modules.tpl.php', $this->projectDir, static::SAMPLE_DIR);
            $this->fileStorage->addFile($source, $target);
        }

        // Add src/Resources/contao/languages/en/default.php to file storage
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/default.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);

        if (!$this->fileStorage->hasFile($target)) {
            $source = sprintf('%s/%s/src/Resources/contao/languages/en/default.tpl.php', $this->projectDir, static::SAMPLE_DIR);
            $this->fileStorage->addFile($source, $target);
        }
    }

    /**
     * Add content element files to the file storage.
     *
     * @throws \Exception
     */
    protected function addContentElementFilesToFileStorage(): void
    {
        // Get the content element template name
        $strContentElementTemplateName = Str::asContaoContentElementTemplateName((string) $this->model->contentelementtype);

        // Get the content element classname
        $strContentElementClassname = Str::asContaoContentElementClassName((string) $this->model->contentelementtype);

        // Add content element class to src/Controller/ContentElement
        $source = sprintf('%s/%s/src/Controller/ContentElement/ContentElementController.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Controller/ContentElement/%s.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $strContentElementClassname);
        $this->fileStorage->addFile($source, $target);

        // Add content element template
        $source = sprintf('%s/%s/src/Resources/contao/templates/ce_sample_element.tpl.html5', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/templates/%s.html5', $this->projectDir, $this->model->vendorname, $this->model->repositoryname, $strContentElementTemplateName);
        $this->fileStorage->addFile($source, $target);

        // Add src/Resources/contao/dca/tl_content.php
        $source = sprintf('%s/%s/src/Resources/contao/dca/tl_content.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/dca/tl_content.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Add src/Resources/contao/languages/en/modules.php to file storage
        $target = sprintf('%s/vendor/%s/%s/src/Resources/contao/languages/en/default.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);

        if (!$this->fileStorage->hasFile($target)) {
            $source = sprintf('%s/%s/src/Resources/contao/languages/en/default.tpl.php', $this->projectDir, static::SAMPLE_DIR);
            $this->fileStorage->addFile($source, $target);
        }
    }

    /**
     * Add custom route to the the file storage.
     *
     * @throws \Exception
     */
    protected function addCustomRouteToFileStorage(): void
    {
        // Add controller (custom route)
        $source = sprintf('%s/%s/src/Controller/Controller.tpl.php', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Controller/MyCustomController.php', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);

        // Add twig template
        $source = sprintf('%s/%s/src/Resources/views/MyCustom/my_custom.html.tpl.twig', $this->projectDir, static::SAMPLE_DIR);
        $target = sprintf('%s/vendor/%s/%s/src/Resources/views/MyCustom/my_custom.html.twig', $this->projectDir, $this->model->vendorname, $this->model->repositoryname);
        $this->fileStorage->addFile($source, $target);
    }

    /**
     * Parse templates.
     *
     * @throws \Exception
     */
    protected function parseTemplates(): void
    {
        $arrFiles = $this->fileStorage->getAll();

        foreach ($arrFiles as $arrFile) {
            $this->fileStorage->getFile($arrFile['target']);

            // Skip images...
            if (isset($arrFile['source']) && !empty($arrFile['source']) && false !== strpos(basename($arrFile['source']), '.tpl.')) {
                $this->fileStorage->replaceTags($this->tagStorage);
            }
        }
    }

    /**
     * Write files from the file storage to the filesystem.
     *
     * @throws \Exception
     *
     * @todo add a contao hook
     */
    protected function createBundleFiles(): void
    {
        $arrFiles = $this->fileStorage->getAll();

        /*
         * @todo add a contao hook here
         * Manipulate, remove or add files to the storage
         */
        foreach ($arrFiles as $arrFile) {
            // Create directory recursive
            if (!is_dir(\dirname($arrFile['target']))) {
                mkdir(\dirname($arrFile['target']), 0777, true);
            }

            // Create file
            file_put_contents($arrFile['target'], $arrFile['content']);

            // Display message in the backend
            $target = str_replace($this->projectDir.'/', '', $arrFile['target']);
            $this->message->addInfo(sprintf('Created file "%s".', $target));
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

        if ('' !== $this->model->editRootComposer) {
            if (!isset($objJSON->repositories)) {
                $objJSON->repositories = [];
            }

            $objRepositories = new \stdClass();

            if ('path' === $this->model->rootcomposerextendrepositorieskey) {
                $objRepositories->type = 'path';
                $objRepositories->url = sprintf('vendor/%s/%s', $this->model->vendorname, $this->model->repositoryname);

                // Prevent duplicate entries
                if (!\in_array($objRepositories, $objJSON->repositories, true)) {
                    $blnModified = true;
                    $objJSON->repositories[] = $objRepositories;
                    $this->message->addInfo('Extended the repositories section in the root composer.json. Please check!');
                }
            }

            if ('vcs-github' === $this->model->rootcomposerextendrepositorieskey) {
                $objRepositories->type = 'vcs';
                $objRepositories->url = sprintf('https://github.com/%s/%s', $this->model->vendorname, $this->model->repositoryname);

                // Prevent duplicate entries
                if (!\in_array($objRepositories, $objJSON->repositories, true)) {
                    $blnModified = true;
                    $objJSON->repositories[] = $objRepositories;
                    $this->message->addInfo('Extended the repositories section in the root composer.json. Please check!');
                }
            }
            // Extend require key
            $blnModified = true;
            $objJSON->require->{sprintf('%s/%s', $this->model->vendorname, $this->model->repositoryname)} = 'dev-master';
            $this->message->addInfo('Extended the require section in the root composer.json. Please check!');
        }

        if ($blnModified) {
            // Make a backup first
            $strBackupPath = sprintf('system/tmp/composer_backup_%s.json', Date::parse('Y-m-d _H-i-s', time()));
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
