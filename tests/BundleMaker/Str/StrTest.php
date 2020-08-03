<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Mein Steckbrief
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-steckbrief-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Str;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;

/**
 * Class StrTest
 *
 * @package Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Str
 */
class StrTest extends ContaoTestCase
{

    public function setUp(): void
    {

        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());
    }

    /**
     * Test if strings are correctly converted to classname format
     */
    public function testAsClassname(): void
    {

        $test = 'foo-Bar_foo__Bar--foo9';
        $actual = 'FooBarFooBarFoo9';
        $this->assertSame(Str::asClassname($test), $actual);

        $test = 'foo-BBar_foo__Bar--foo9';
        $actual = 'FooBBarFooBarFoo9';
        $this->assertSame(Str::asClassname($test), $actual);

        $test = 'FFFbF';
        $actual = 'FFFbF';
        $this->assertSame(Str::asClassname($test), $actual);

        $test = 'my_custom name-space';
        $actual = 'MyCustomNameSpace';
        $this->assertSame(Str::asClassname($test), $actual);

        $test = 'foo_Bar_fooBar99';
        $actual = 'FooBarFooBar99';
        $this->assertSame(Str::asClassname($test), $actual);
    }

    /**
     *
     */
    public function testAddPrefix()
    {

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
     * Test if strings are correctly converted to snakecase format
     */
    public function testAsSnakecase(): void
    {

        $test = 'foo-Bar_foo__Bar--foo 9';
        $actual = 'foo_bar_foo_bar_foo_9';
        $this->assertSame(Str::asSnakecase($test), $actual);
    }

    /**
     * Test if strings are correctly converted to github vendorname format
     */
    public function testAsVendorname(): void
    {

        $test = 'vendor_Name8';
        $actual = 'vendor-Name8';
        $this->assertSame(Str::asVendorname($test), $actual);

        $test = '-vendor--name-';
        $actual = 'vendor-name';
        $this->assertSame(Str::asVendorname($test), $actual);

        $test = '--vendor_name--';
        $actual = 'vendor-name';
        $this->assertSame(Str::asVendorname($test), $actual);
    }

    /**
     * Test if strings are correctly converted to github repository name format
     */
    public function testAsRepositoryname(): void
    {

        $test = 'repository_-name#?';
        $actual = 'repository_-name--';
        $this->assertSame(Str::asRepositoryname($test), $actual);
    }

    /**
     * Test if strings are correctly converted to frontend module type format
     */
    public function testAsContaoFrontendModuleType(): void
    {

        $test = 'my_ Custom_module';
        $actual = 'my_custom_module';
        $this->assertSame(Str::asContaoFrontendModuleType($test), $actual);

        $test = 'my_ Custom99_';
        $actual = 'my_custom99_module';
        $this->assertSame(Str::asContaoFrontendModuleType($test), $actual);
    }

    /**
     * Test if strings are correctly converted to backend module type format
     */
    public function testAsContaoBackendModuleType(): void
    {

        $test = 'foo-Bar_foo__Bar--foo 9';
        $actual = 'foo_bar_foo_bar_foo_9';
        $this->assertSame(Str::asContaoBackendModuleType($test), $actual);
    }

    /**
     * Test if strings are correctly converted to the dca table format
     *
     * @throws \Exception
     */
    public function testAsContaoDcaTableName(): void
    {

        $test = 'foo-Bar_foo_ _Bar--foo 9';
        $actual = 'tl_foo_bar_foo_bar_foo_9';
        $this->assertSame(Str::asContaoDcaTableName($test), $actual);

        $test = 'tl_foo-Bar_foo_ _Bar--foo 9';
        $actual = 'tl_foo_bar_foo_bar_foo_9';
        $this->assertSame(Str::asContaoDcaTableName($test), $actual);
    }

    /**
     * Test if strings are correctly converted to frontend module classname format
     */
    public function testAsContaoFrontendModuleClassname(): void
    {

        $test = 'my_ --ExtraCustom--99_Module';
        $actual = 'MyExtraCustom99ModuleController';
        $this->assertSame(Str::asContaoFrontendModuleClassname($test, 'Controller'), $actual);

        $test = 'my_ --ExtraCustom--99_';
        $actual = 'MyExtraCustom99ModuleController';
        $this->assertSame(Str::asContaoFrontendModuleClassname($test, 'Controller'), $actual);
    }

    /**
     * Test if strings are correctly converted to model classname format
     *
     * @throws \Exception
     */
    public function testAsContaoModelClassname(): void
    {

        $test = 'tl_my_ table';
        $actual = 'MyTableModel';
        $this->assertSame(Str::asContaoModelClassname($test), $actual);
    }

    /**
     * Test if strings are correctly converted to frontend module template format
     */
    public function testAsContaoFrontendModuleTemplateName(): void
    {

        $test = 'mod_my_ Custom_module';
        $actual = 'mod_my_custom';
        $this->assertSame(Str::asContaoFrontendModuleTemplateName($test, 'mod_'), $actual);

        $test = '_my_ Custom_module';
        $actual = 'mod_my_custom';
        $this->assertSame(Str::asContaoFrontendModuleTemplateName($test, 'mod_'), $actual);
    }

}
