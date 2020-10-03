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

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Str;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;

/**
 * Class StrTest.
 */
class StrTest extends ContaoTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());

      
    }

    protected function tearDown(): void
    {
        if (true === file_exists($this->tmpPhpdocFile)) {
            unlink($this->tmpPhpdocFile);
        }
    }

    /**
     * Test if strings are correctly converted to classname format.
     */
    public function testAsClassName(): void
    {
        $test = 'foo-Bar_foo__Bar--foo9';
        $actual = 'FooBarFooBarFoo9';
        $this->assertSame(Str::asClassName($test), $actual);

        $test = 'foo-BBar_foo__Bar--foo9';
        $actual = 'FooBBarFooBarFoo9';
        $this->assertSame(Str::asClassName($test), $actual);

        $test = 'FFFbF';
        $actual = 'FFFbF';
        $this->assertSame(Str::asClassName($test), $actual);

        $test = 'my_custom name-space';
        $actual = 'MyCustomNameSpace';
        $this->assertSame(Str::asClassName($test), $actual);

        $test = 'foo_Bar_fooBar99';
        $actual = 'FooBarFooBar99';
        $this->assertSame(Str::asClassName($test), $actual);
    }

    public function testAddPrefix(): void
    {
        $testValue = 'custom-bundle';
        $testPrefix = '';
        $actual = 'custom-bundle';
        $this->assertSame(Str::addPrefix($testValue, $testPrefix), $actual);

        $testValue = 'custom-bundle';
        $testPrefix = 'contao-';
        $actual = 'contao-custom-bundle';
        $this->assertSame(Str::addPrefix($testValue, $testPrefix), $actual);

        $testValue = 'contao-custom-bundle';
        $testPrefix = 'contao-';
        $actual = 'contao-custom-bundle';
        $this->assertSame(Str::addPrefix($testValue, $testPrefix), $actual);

        $testValue = 'Contao-custom-bundle';
        $testPrefix = 'contao-';
        $actual = 'contao-custom-bundle';
        $this->assertSame(Str::addPrefix($testValue, $testPrefix), $actual);

        $testValue = 'Contao-contao-custom-bundle';
        $testPrefix = 'contao-';
        $actual = 'contao-contao-custom-bundle';
        $this->assertSame(Str::addPrefix($testValue, $testPrefix), $actual);
    }

    /**
     * Test if strings are correctly converted to snakecase format.
     */
    public function testAsSnakeCase(): void
    {
        $test = 'foo-Bar_foo__Bar--foo 9';
        $actual = 'foo_bar_foo_bar_foo_9';
        $this->assertSame(Str::asSnakeCase($test), $actual);
    }

    /**
     * Test if strings are correctly converted to github vendorname format.
     */
    public function testAsVendorName(): void
    {
        $test = 'vendor_Name8';
        $actual = 'vendor-Name8';
        $this->assertSame(Str::asVendorName($test), $actual);

        $test = '-vendor--name-';
        $actual = 'vendor-name';
        $this->assertSame(Str::asVendorName($test), $actual);

        $test = '--vendor_name--';
        $actual = 'vendor-name';
        $this->assertSame(Str::asVendorName($test), $actual);
    }

    /**
     * Test if strings are correctly converted to dependency injection extension class name.
     */
    public function testAsDependencyInjectionExtensionClassName(): void
    {
        $vendorName = 'dirty_harry';
        $repositoryName = 'contao-super-bundle';
        $actual = 'DirtyHarryContaoSuperExtension';
        $this->assertSame(Str::asDependencyInjectionExtensionClassName($vendorName, $repositoryName), $actual);
    }

    /**
     * Test if strings are correctly converted to github repository name format.
     */
    public function testAsRepositoryName(): void
    {
        $test = 'repository_-name#?';
        $actual = 'repository_-name--';
        $this->assertSame(Str::asRepositoryName($test), $actual);
    }

    /**
     * Test if strings are correctly converted to frontend module type format.
     */
    public function testAsContaoFrontendModuleType(): void
    {
        $test = 'my_ Custom_module';
        $actual = 'my_custom_module';
        $this->assertSame(Str::asContaoFrontendModuleType($test), $actual);

        $test = 'my_ Custom99___';
        $actual = 'my_custom99_module';
        $this->assertSame(Str::asContaoFrontendModuleType($test), $actual);
    }

    /**
     * Test if strings are correctly converted to backend module type format.
     */
    public function testAsContaoBackendModuleType(): void
    {
        $test = 'foo-Bar_foo__Bar--foo 9';
        $actual = 'foo_bar_foo_bar_foo_9';
        $this->assertSame(Str::asContaoBackendModuleType($test), $actual);
    }

    /**
     * Test if strings are correctly converted to the dca table format.
     *
     * @throws \Exception
     */
    public function testAsContaoDcaTableName(): void
    {
        $test = 'foo-Bar_foo_ _Bar--foo 9';
        $actual = 'tl_foo_bar_foo_bar_foo_9';
        $this->assertSame(Str::asContaoDcaTable($test), $actual);

        $test = 'tl_foo-Bar_foo_ _Bar--foo 9';
        $actual = 'tl_foo_bar_foo_bar_foo_9';
        $this->assertSame(Str::asContaoDcaTable($test), $actual);
    }

    /**
     * Test if strings are correctly converted to frontend module classname format.
     */
    public function testAsContaoFrontendModuleClassName(): void
    {
        $test = 'my_ --ExtraCustom--99_Module';
        $actual = 'MyExtraCustom99ModuleController';
        $this->assertSame(Str::asContaoFrontendModuleClassName($test, 'Controller'), $actual);

        $test = 'my_ --ExtraCustom--99_';
        $actual = 'MyExtraCustom99ModuleController';
        $this->assertSame(Str::asContaoFrontendModuleClassName($test, 'Controller'), $actual);
    }

    /**
     * Test if strings are correctly converted to model classname format.
     *
     * @throws \Exception
     */
    public function testAsContaoModelClassName(): void
    {
        $test = 'tl_my_ table';
        $actual = 'MyTableModel';
        $this->assertSame(Str::asContaoModelClassName($test), $actual);
    }

    /**
     * Test if strings are correctly converted to frontend module template format.
     */
    public function testAsContaoFrontendModuleTemplateName(): void
    {
        $test = 'mod_my_ Custom_module';
        $actual = 'mod_my_custom_module';
        $this->assertSame(Str::asContaoFrontendModuleTemplateName($test, 'mod_'), $actual);

        $test = '_my_ Custom_module';
        $actual = 'mod_my_custom_module';
        $this->assertSame(Str::asContaoFrontendModuleTemplateName($test, 'mod_'), $actual);
    }

    /**
     * Test if method returns the correct twig namespace.
     */
    public function testAsTwigNamespace(): void
    {
        $vendorName = 'dirty_harry';
        $repositoryName = 'contao-super-bundle';
        $actual = '@DirtyHarryContaoSuper';
        $this->assertSame(Str::asTwigNamespace($vendorName, $repositoryName), $actual);
    }


}
