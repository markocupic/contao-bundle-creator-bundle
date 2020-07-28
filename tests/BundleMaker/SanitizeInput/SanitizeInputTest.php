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

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\SanitizeInput;

use Contao\TestCase\ContaoTestCase;
use Contao\System;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\SanitizeInput\SanitizeInput;

/**
 * Class SanitizeInputTest
 * @package Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\SanitizeInput
 */
class SanitizeInputTest extends ContaoTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());
    }

    /**
     * Test if strings are correctly converted to psr4 namespace format
     */
    public function testToPsr4Namespace()
    {
        $test = 'foo-Bar_foo__Bar--foo9';
        $actual = 'FooBarFooBarFoo9';
        $this->assertSame(SanitizeInput::toPsr4Namespace($test), $actual);

        $test = 'foo-BBar_foo__Bar--foo9';
        $actual = 'FooBBarFooBarFoo9';
        $this->assertSame(SanitizeInput::toPsr4Namespace($test), $actual);

        $test = 'FFFbF';
        $actual = 'FFFbF';
        $this->assertSame(SanitizeInput::toPsr4Namespace($test), $actual);

        $test = 'my_custom name-space';
        $actual = 'MyCustomNameSpace';
        $this->assertSame(SanitizeInput::toPsr4Namespace($test), $actual);

        $test = 'foo_Bar_fooBar99';
        $actual = 'FooBarFooBar99';
        $this->assertSame(SanitizeInput::toPsr4Namespace($test), $actual);
    }

    /**
     * Test if strings are correctly converted to snakecase format
     */
    public function testToSnakecase()
    {
        $test = 'foo-Bar_foo__Bar--foo 9';
        $actual = 'foo_bar_foo_bar_foo_9';
        $this->assertSame(SanitizeInput::toSnakecase($test), $actual);
    }

    /**
     * Test if strings are correctly converted to frontend module type format
     */
    public function testGetSanitizedFrontendModuleType()
    {
        $test = 'my_ Custom_module';
        $actual = 'my_custom_module';
        $this->assertSame(SanitizeInput::getSanitizedFrontendModuleType($test), $actual);

        $test = 'my_ Custom99_';
        $actual = 'my_custom99_module';
        $this->assertSame(SanitizeInput::getSanitizedFrontendModuleType($test), $actual);
    }

    /**
     * Test if strings are correctly converted to backend module type format
     */
    public function testGetSanitizedBackendModuleType()
    {
        $test = 'foo-Bar_foo__Bar--foo 9';
        $actual = 'foo_bar_foo_bar_foo_9';
        $this->assertSame(SanitizeInput::getSanitizedBackendModuleType($test), $actual);
    }

    /**
     * Test if strings are correctly converted to the dca table format
     */
    public function testGetSanitizedDcaTableName()
    {
        $test = 'foo-Bar_foo_ _Bar--foo 9';
        $actual = 'tl_foo_bar_foo_bar_foo_9';
        $this->assertSame(SanitizeInput::getSanitizedDcaTableName($test), $actual);

        $test = 'tl_foo-Bar_foo_ _Bar--foo 9';
        $actual = 'tl_foo_bar_foo_bar_foo_9';
        $this->assertSame(SanitizeInput::getSanitizedDcaTableName($test), $actual);
    }

    /**
     * Get the frontend module classname from module type and add the "Controller" postfix
     * f.ex. my_custom_module => MyCustomModuleController
     *
     * @param string $str (requires tl_contao_bundle_creator.frontendmoduletype)
     * @param string $postfix
     * @return string
     */
    public static function getSanitizedFrontendModuleClassname(string $str, string $postfix = 'Controller'): string
    {
        $str = static::getSanitizedFrontendModuleType($str);
        $str = static::toPsr4Namespace($str);
        return $str . $postfix;
    }

    /**
     * Test if strings are correctly converted to frontend module classname format
     */
    public function testGetSanitizedFrontendModuleClassname()
    {
        $test = 'my_ --ExtraCustom--99_Module';
        $actual = 'MyExtraCustom99ModuleController';
        $this->assertSame(SanitizeInput::getSanitizedFrontendModuleClassname($test, 'Controller'), $actual);

        $test = 'my_ --ExtraCustom--99_';
        $actual = 'MyExtraCustom99ModuleController';
        $this->assertSame(SanitizeInput::getSanitizedFrontendModuleClassname($test, 'Controller'), $actual);
    }

    /**
     * Test if strings are correctly converted to model classname format
     */
    public function testGetSanitizedModelClassname()
    {
        $test = 'tl_my_ table';
        $actual = 'MyTableModel';
        $this->assertSame(SanitizeInput::getSanitizedModelClassname($test), $actual);
    }

    /**
     * Test if strings are correctly converted to frontend module template format
     */
    public function testGetSanitizedFrontendModuleTemplateName()
    {
        $test = 'mod_my_ Custom_module';
        $actual = 'mod_my_custom';
        $this->assertSame(SanitizeInput::getSanitizedFrontendModuleTemplateName($test, 'mod_'), $actual);

        $test = '_my_ Custom_module';
        $actual = 'mod_my_custom';
        $this->assertSame(SanitizeInput::getSanitizedFrontendModuleTemplateName($test, 'mod_'), $actual);
    }

}
