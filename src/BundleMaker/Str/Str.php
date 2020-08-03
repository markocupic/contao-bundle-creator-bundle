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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str;

/**
 * Class String
 *
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str
 */
final class Str
{

    /**
     * Looks for prefixes in strings in a case-insensitive way.
     *
     * @param string $value
     * @param string $prefix
     * @return bool
     */
    public static function hasPrefix(string $value, string $prefix): bool
    {

        return 0 === stripos($value, $prefix);
    }

    /**
     * Ensures that the given string ends with the given prefix. If the string
     * already contains the prefix, it's not added twice. It's case-insensitive
     * (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'FooCommand').
     */
    public static function addPrefix(string $value, string $prefix): string
    {

        return $prefix . self::removePrefix($value, $prefix);
    }

    /**
     * Ensures that the given string doesn't starts with the given prefix. If the
     * string contains the prefix multiple times, only the first one is removed.
     * It's case-insensitive (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'Foo'.
     */
    public static function removePrefix(string $value, string $prefix): string
    {

        return self::hasPrefix($value, $prefix) ? substr($value, strlen($prefix)) : $value;
    }

    /**
     * Sanitize vendorname (github restrictions)
     * Do no allow: vendor_name, -vendorname, vendorname-, vendor--name
     * But allow Vendor-Name, vendor-name
     *
     * @param string $str
     * @return string
     */
    public static function asVendorname(string $str): string
    {

        $str = preg_replace('/_/', '-', $str);
        $str = preg_replace('/[\-]{2,}/', '-', $str);
        $str = preg_replace('/^-+|_+|[^A-Za-z0-9\-]|-+$/', '', $str);
        return $str;
    }

    /**
     * Sanitize repository name (github restrictions)
     * "vendor_name#" will be converted to "vendor_name-"
     *
     * @param string $str
     * @return string
     */
    public static function asRepositoryname(string $str): string
    {

        return preg_replace('/[^A-Za-z0-9_\-]/', '-', $str);
    }

    /**
     * Get the backend module type (f.ex. my_custom_module)
     * Convention => snakecase
     *
     * @param string $str (requires tl_contao_bundle_creator.backendmoduletype)
     * @return string
     */
    public static function asContaoBackendModuleType(string $str): string
    {

        $str = self::asSnakecase($str);
        return $str;
    }

    /**
     * Converts a string to snakecase
     * My custom module => my_custom_module
     *
     * @param string $str
     * @return string
     */
    public static function asSnakecase(string $str): string
    {

        $str = str_replace('/[^A-Za-z0-9_\-]/', '', $str);
        $str = str_replace(' ', '_', $str);
        $str = str_replace('-', '_', $str);

        // Split between uppercase letters FooBBar -> Foo_B_Bar
        $pieces = preg_split('/(?=[A-Z])/', $str);
        $pieces = array_map(function ($str) {

            return '_' . $str;
        }, $pieces);
        $str = implode($pieces);

        // Trim from underscores
        $str = preg_replace('/^_|_$/', '', $str);
        // Do not allow multiple underscores in series
        $str = preg_replace('/_{2,}/', '_', $str);
        $str = strtolower($str);

        return $str;
    }

    /**
     * Get the frontend module classname from module type and add the "Controller" postfix
     * f.ex. my_custom_module => MyCustomModuleController
     *
     * @param string $str (requires tl_contao_bundle_creator.frontendmoduletype)
     * @param string $postfix
     * @return string
     */
    public static function asContaoFrontendModuleClassname(string $str, string $postfix = 'Controller'): string
    {

        $str = self::asContaoFrontendModuleType($str);
        $str = self::asClassname($str);
        return $str . $postfix;
    }

    /**
     * Get the backend module type (f.ex. my_custom_module)
     * Convention => snakecase
     *
     * @return string
     */

    /**
     * Get the frontend module type (f.ex. my_custom_module)
     * Convention => snakecase with postfix "_module"
     *
     * @param string $str (requires tl_contao_bundle_creator.frontendmoduletype)
     * @param string $postfix
     * @return string
     */
    public static function asContaoFrontendModuleType(string $str, $postfix = '_module'): string
    {

        $str = self::asSnakecase((string) $str);

        $pattern = '/^(module_|module|mod_|mod)/';
        if (preg_match($pattern, $str))
        {
            $str = preg_replace($pattern, '', $str);
        }

        $pattern = '/(_module|module)$/';
        if (preg_match($pattern, $str))
        {
            $str = preg_replace($pattern, '', $str);
        }

        // Add postfix
        $str = $str . $postfix;

        return $str;
    }

    /**
     * Converts a string to namespace format
     * "my_custom name-space" becomes "MyCustomNameSpace"
     * "foo_Bar_fooBar99" becomes "FooBarFooBar99"
     *
     * @param string $str
     * @return string
     */
    public static function asClassname(string $str): string
    {

        $str = str_replace('/[^A-Za-z0-9_\-]/', '', $str);
        $str = str_replace('-', '_', $str);
        $str = str_replace(' ', '_', $str);

        // Split where Uppercase letter begins fooBar -> foo_Bar
        $pieces = preg_split('/(?=[A-Z\s]+)/', $str);
        $pieces = array_map(function ($str) {

            return '_' . $str;
        }, $pieces);
        $str = implode($pieces);

        // Split between uppercase letters FooBBar -> Foo_B_Bar
        $pieces = preg_split('/(?=[A-Z])/', $str);
        $pieces = array_map(function ($str) {

            return '_' . $str;
        }, $pieces);
        $str = implode($pieces);

        // Trim from underscores
        $str = preg_replace('/^_|_$/', '', $str);

        // Do not allow multiple underscores in series
        $str = preg_replace('/_{2,}/', '_', $str);

        $arrNamespace = explode('_', $str);
        $arrNamespace = array_filter($arrNamespace, 'strlen');
        $arrNamespace = array_map('strtolower', $arrNamespace);
        $arrNamespace = array_map('ucfirst', $arrNamespace);
        return implode('', $arrNamespace);
    }

    /**
     * Get model classname f.ex. SampleTable
     *
     * @param string $str (requires tl_contao_bundle_creator.dcatable)
     * @param string $postfix
     * @return string
     * @throws \Exception
     */
    public static function asContaoModelClassname(string $str, string $postfix = 'Model'): string
    {

        $str = self::asContaoDcaTableName($str);
        $str = preg_replace('/^tl_/', '', $str);
        $str = self::asClassname($str);
        return $str . $postfix;
    }

    /**
     * Get the sanitized dca tablename f.ex. tl_sample_table
     *
     * @param string $str (requires tl_contao_bundle_creator.dcatable)
     * @return string
     * @throws \Exception
     */
    public static function asContaoDcaTableName(string $str): string
    {

        if (!strlen((string) $str))
        {
            throw new \Exception('No dca tablename set.');
        }

        $str = strtolower($str);
        $str = preg_replace('/-|\s/', '_', $str);
        $str = preg_replace('/_{2,}/', '_', $str);
        $str = preg_replace('/[^A-Za-z0-9_]|_$/', '', $str);
        if (!preg_match('/^tl_/', $str))
        {
            $str = 'tl_' . $str;
        }
        return $str;
    }

    /**
     * Get the frontend module template name from the frontend module type and add the prefix "mod_"
     *
     * @param string $strPrefix
     * @return string $str (requires tl_contao_bundle_creator.frontendmoduletype)
     * @return string
     */

    /**
     * @param string $str
     * @param string $strPrefix
     * @return string
     */
    public static function asContaoFrontendModuleTemplateName(string $str, $strPrefix = 'mod_'): string
    {

        $str = self::asContaoFrontendModuleType($str);
        if ($strPrefix != '')
        {
            $str = preg_replace('/^' . $strPrefix . '/', '', $str);
        }
        $str = preg_replace('/_module$/', '', $str);
        $str = preg_replace('/_module$/', '', $str);
        $str = $strPrefix . $str;
        $str = preg_replace('/_{2,}/', '_', $str);

        return $str;
    }
}
