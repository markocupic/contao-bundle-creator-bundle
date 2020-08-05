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
     *
     * @param string $value
     * @param string $prefix
     * @return string
     */
    public static function addPrefix(string $value, string $prefix): string
    {

        return $prefix . self::removePrefix($value, $prefix);
    }

    /**
     * Looks for suffixes in strings in a case-insensitive way.
     *
     * @param string $value
     * @param string $suffix
     * @return bool
     */
    public static function hasSuffix(string $value, string $suffix): bool
    {

        return 0 === strcasecmp($suffix, substr($value, -\strlen($suffix)));
    }

    /**
     * Ensures that the given string ends with the given suffix. If the string
     * already contains the suffix, it's not added twice. It's case-insensitive
     * (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'FooCommand').
     *
     * @param string $value
     * @param string $suffix
     * @return string
     */
    public static function addSuffix(string $value, string $suffix): string
    {

        return self::removeSuffix($value, $suffix) . $suffix;
    }

    /**
     * Ensures that the given string doesn't end with the given suffix. If the
     * string contains the suffix multiple times, only the last one is removed.
     * It's case-insensitive (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'Foo'.
     *
     * @param string $value
     * @param string $suffix
     * @return string
     */
    public static function removeSuffix(string $value, string $suffix): string
    {

        return self::hasSuffix($value, $suffix) ? substr($value, 0, -\strlen($suffix)) : $value;
    }

    /**
     * Ensures that the given string doesn't starts with the given prefix. If the
     * string contains the prefix multiple times, only the first one is removed.
     * It's case-insensitive (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'Foo'.
     *
     * @param string $value
     * @param string $prefix
     * @return string
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
     * @param string $value
     * @return string
     */
    public static function asVendorName(string $value): string
    {

        $value = preg_replace('/_/', '-', $value);
        $value = preg_replace('/[\-]{2,}/', '-', $value);
        $value = preg_replace('/^-+|_+|[^A-Za-z0-9\-]|-+$/', '', $value);
        return $value;
    }

    /**
     * Sanitize repository name (github restrictions)
     * Remove not accepted strings and replace them with "-"
     * contao-my-repository#" will be converted to "contao-my-repository-"
     *
     * @param string $value
     * @param string $prefix
     * @return string
     */
    public static function asRepositoryName(string $value, string $prefix = ''): string
    {
        $value = str_replace('#', '-', $value);
        $value = preg_replace('/[^A-Za-z0-9_\-]/', '-', $value);
        return self::addPrefix($value, $prefix);
    }

    /**
     * Get the backend module type (f.ex. my_custom_module)
     * Convention => snakecase
     *
     * @param string $value (requires tl_contao_bundle_creator.backendmoduletype)
     * @return string
     */
    public static function asContaoBackendModuleType(string $value): string
    {

        return self::asSnakeCase($value);
    }

    /**
     * Converts a string to snakecase
     * My custom module => my_custom_module
     *
     * @param string $value
     * @return string
     */
    public static function asSnakeCase(string $value): string
    {

        $value = trim($value);
        $value = preg_replace('/[^a-zA-Z0-9_]/', '_', $value);
        $value = preg_replace('/(?<=\\w)([A-Z])/', '_$1', $value);
        $value = preg_replace('/_{2,}/', '_', $value);

        return strtolower($value);
    }

    /**
     * Get the frontend module classname from module type and add the "Controller" suffix
     * f.ex. my_custom_module => MyCustomModuleController
     *
     * @param string $value (requires tl_contao_bundle_creator.frontendmoduletype)
     * @param string $suffix
     * @return string
     */
    public static function asContaoFrontendModuleClassName(string $value, string $suffix = 'Controller'): string
    {

        $value = self::asContaoFrontendModuleType($value);
        $value = self::asClassName($value);
        return self::addSuffix($value, $suffix);
    }

    /**
     * Get the frontend module type (f.ex. my_custom_module)
     * Convention => snakecase with suffix "_module"
     *
     * @param string $value (requires tl_contao_bundle_creator.frontendmoduletype)
     * @param string $suffix
     * @return string
     */
    public static function asContaoFrontendModuleType(string $value, $suffix = '_module'): string
    {

        $value = self::asSnakeCase((string) $value);

        $pattern = '/^(module_|module|mod_|mod|_{1})/';
        $value = preg_replace($pattern, '', $value);

        $pattern = '/_{1}$/';
        $value = preg_replace($pattern, '', $value);

        // Add suffix
        $value = self::addSuffix($value, $suffix);

        return $value;
    }

    /**
     * Transforms the given string into the format commonly used by PHP classes,
     * (e.g. `app:do_this-and_that` -> `AppDoThisAndThat`) but it doesn't check
     * the validity of the class name.
     *
     * @param string $value
     * @param string $suffix
     * @return string
     */
    public static function asClassName(string $value, string $suffix = ''): string
    {

        $value = trim($value);
        $value = str_replace(['-', '_', '.', ':'], ' ', $value);
        $value = ucwords($value);
        $value = str_replace(' ', '', $value);
        $value = ucfirst($value);
        $value = self::addSuffix($value, $suffix);

        return $value;
    }

    /**
     * Get model classname f.ex. SampleTable
     *
     * @param string $value (requires tl_contao_bundle_creator.dcatable)
     * @param string $suffix
     * @return string
     * @throws \Exception
     */
    public static function asContaoModelClassName(string $value, string $suffix = 'Model'): string
    {

        $value = self::asContaoDcaTable($value);
        $value = preg_replace('/^tl_/', '', $value);
        return self::asClassName($value, $suffix);
    }

    /**
     * Get the sanitized dca tablename f.ex. tl_sample_table
     *
     * @param string $value (requires tl_contao_bundle_creator.dcatable)
     * @return string
     * @throws \Exception
     */
    public static function asContaoDcaTable(string $value): string
    {

        if (!strlen((string) $value))
        {
            throw new \Exception('No dca tablename set.');
        }

        $value = strtolower($value);
        $value = preg_replace('/-|\s/', '_', $value);
        $value = preg_replace('/_{2,}/', '_', $value);
        $value = preg_replace('/[^A-Za-z0-9_]|_$/', '', $value);
        if (!preg_match('/^tl_/', $value))
        {
            $value = 'tl_' . $value;
        }
        return $value;
    }

    /**
     * Get the frontend module template name from the frontend module type and add the prefix "mod_"
     *
     * @param string $valuePrefix
     * @return string $value (requires tl_contao_bundle_creator.frontendmoduletype)
     * @return string
     */

    /**
     * @param string $value
     * @param string $valuePrefix
     * @return string
     */
    public static function asContaoFrontendModuleTemplateName(string $value, $prefix = 'mod_'): string
    {

        $value = self::asContaoFrontendModuleType($value);
        $value = self::addPrefix($value, $prefix);
        $value = preg_replace('/_{2,}/', '_', $value);

        return $value;
    }
}
