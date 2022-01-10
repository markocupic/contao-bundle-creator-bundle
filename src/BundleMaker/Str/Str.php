<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str;

/**
 * Class String.
 */
final class Str
{
    /**
     * Sanitize vendorname (github 6 packagist restrictions)
     * Do no allow: vendor_name, -vendorname, vendorname-, vendor--name, Vendorname
     * But allow vendor-name.
     */
    public static function asVendorName(string $value): string
    {
        $value = preg_replace('/_/', '-', $value);
        $value = preg_replace('/[\-]{2,}/', '-', $value);
        $value = preg_replace('/^-+|_+|[^A-Za-z0-9\-]|-+$/', '', $value);

        return strtolower($value);
    }

    /**
     * Sanitize repository name (github restrictions)
     * Remove not accepted strings and replace them with "-"
     * contao-my-repository#" will be converted to "contao-my-repository-".
     */
    public static function asRepositoryName(string $value, string $prefix = ''): string
    {
        $value = str_replace('#', '-', $value);
        $value = preg_replace('/[^A-Za-z0-9_\-]/', '-', $value);

        return self::addPrefix($value, $prefix);
    }

    /**
     * Ensures that the given string ends with the given prefix. If the string
     * already contains the prefix, it's not added twice. It's case-insensitive
     * (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'FooCommand').
     */
    public static function addPrefix(string $value, string $prefix): string
    {
        return $prefix.self::removePrefix($value, $prefix);
    }

    /**
     * Ensures that the given string doesn't starts with the given prefix. If the
     * string contains the prefix multiple times, only the first one is removed.
     * It's case-insensitive (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'Foo'.
     */
    public static function removePrefix(string $value, string $prefix): string
    {
        return self::hasPrefix($value, $prefix) ? substr($value, \strlen($prefix)) : $value;
    }

    /**
     * Looks for prefixes in strings in a case-insensitive way.
     */
    public static function hasPrefix(string $value, string $prefix): bool
    {
        return 0 === stripos($value, $prefix);
    }

    /**
     * Sanitize composer description text
     * Replace double quotes with single quotes.
     */
    public static function asComposerDescription(string $value): string
    {
        return $value = str_replace('"', "'", $value);
    }

    /**
     * Get the backend module type (f.ex. my_custom_module)
     * Convention => snakecase.
     *
     * @param string $value (requires tl_contao_bundle_creator.backendmoduletype)
     */
    public static function asContaoBackendModuleType(string $value): string
    {
        return self::asSnakeCase($value);
    }

    /**
     * Converts a string to snakecase
     * My custom module => my_custom_module.
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
     * Return Dependeny Injection Extension Classname
     * e.g. ContaoCalendarExtension.
     *
     * @return string|array<string>|null
     */
    public static function asDependencyInjectionExtensionClassname(string $vendorName, string $repositoryName)
    {
        return preg_replace('/Bundle$/', '',
                self::asClassName($vendorName).self::asClassName($repositoryName)).'Extension';
    }

    /**
     * Transforms the given string into the format commonly used by PHP classes,
     * (e.g. `app:do_this-and_that` -> `AppDoThisAndThat`) but it doesn't check
     * the validity of the class name.
     */
    public static function asClassName(string $value, string $suffix = ''): string
    {
        $value = trim($value);
        $value = str_replace(['-', '_', '.', ':'], ' ', $value);
        $value = ucwords($value);
        $value = str_replace(' ', '', $value);
        $value = ucfirst($value);

        return self::addSuffix($value, $suffix);
    }

    /**
     * Ensures that the given string ends with the given suffix. If the string
     * already contains the suffix, it's not added twice. It's case-insensitive
     * (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'FooCommand').
     */
    public static function addSuffix(string $value, string $suffix): string
    {
        return self::removeSuffix($value, $suffix).$suffix;
    }

    /**
     * Ensures that the given string doesn't end with the given suffix. If the
     * string contains the suffix multiple times, only the last one is removed.
     * It's case-insensitive (e.g. value: 'Foocommand' suffix: 'Command' -> result: 'Foo'.
     */
    public static function removeSuffix(string $value, string $suffix): string
    {
        return self::hasSuffix($value, $suffix) ? substr($value, 0, -\strlen($suffix)) : $value;
    }

    /**
     * Looks for suffixes in strings in a case-insensitive way.
     */
    public static function hasSuffix(string $value, string $suffix): bool
    {
        return 0 === strcasecmp($suffix, substr($value, -\strlen($suffix)));
    }

    /**
     * Get the frontend module classname from module type and add the "Controller" suffix
     * f.ex. my_custom_module => MyCustomModuleController.
     *
     * @param string $value (requires tl_contao_bundle_creator.frontendmoduletype)
     */
    public static function asContaoFrontendModuleClassName(string $value, string $suffix = 'Controller'): string
    {
        $value = self::asContaoFrontendModuleType($value);
        $value = self::asClassName($value);

        return self::addSuffix($value, $suffix);
    }

    /**
     * Get the frontend module type (f.ex. my_custom)
     * Convention => snakecase.
     *
     * @param string $value (requires tl_contao_bundle_creator.frontendmoduletype)
     * @param string $suffix (add a suffix e.g. "_module")
     */
    public static function asContaoFrontendModuleType(string $value, $suffix = ''): string
    {
        $value = self::asSnakeCase((string)$value);

        $pattern = '/^(module_|module|mod_|mod|_{1})/';
        $value = preg_replace($pattern, '', $value);

        $pattern = '/_{1}$/';
        $value = preg_replace($pattern, '', $value);

        // Add suffix
        return self::addSuffix($value, $suffix);
    }

    /**
     * Get the content element classname from element type and add the "Controller" suffix
     * f.ex. my_custom_element => MyCustomElementController.
     *
     * @param string $value (requires tl_contao_bundle_creator.contentelementtype)
     */
    public static function asContaoContentElementClassName(string $value, string $suffix = 'Controller'): string
    {
        $value = self::asContaoContentElementType($value);
        $value = self::asClassName($value);

        return self::addSuffix($value, $suffix);
    }

    /**
     * Get the content element type (f.ex. my_custom)
     * Convention => snakecase.
     *
     * @param string $value (requires tl_contao_bundle_creator.contentelementtype)
     * @param string $suffix (add a suffix e.g. "_element")
     */
    public static function asContaoContentElementType(string $value, $suffix = ''): string
    {
        $value = self::asSnakeCase((string)$value);

        $pattern = '/^(element_|element|ce_|ce|_{1})/';
        $value = preg_replace($pattern, '', $value);

        $pattern = '/_{1}$/';
        $value = preg_replace($pattern, '', $value);

        // Add suffix
        return self::addSuffix($value, $suffix);
    }

    /**
     * Get model classname f.ex. SampleTable.
     *
     * @param string $value (requires tl_contao_bundle_creator.dcatable)
     *
     * @throws \Exception
     */
    public static function asContaoModelClassName(string $value, string $suffix = 'Model'): string
    {
        $value = self::asContaoDcaTable($value);
        $value = preg_replace('/^tl_/', '', $value);

        return self::asClassName($value, $suffix);
    }

    /**
     * Get the sanitized dca tablename f.ex. tl_sample_table.
     *
     * @param string $value (requires tl_contao_bundle_creator.dcatable)
     *
     * @throws \Exception
     */
    public static function asContaoDcaTable(string $value): string
    {
        if (!\strlen((string)$value)) {
            throw new \Exception('No dca tablename set.');
        }

        $value = strtolower($value);
        $value = preg_replace('/-|\s/', '_', $value);
        $value = preg_replace('/_{2,}/', '_', $value);
        $value = preg_replace('/[^A-Za-z0-9_]|_$/', '', $value);

        if (!preg_match('/^tl_/', $value)) {
            $value = 'tl_'.$value;
        }

        return $value;
    }

    public static function asContaoFrontendModuleTemplateName(string $value, $prefix = 'mod_'): string
    {
        $value = self::asContaoFrontendModuleType($value);
        $value = self::addPrefix($value, $prefix);

        return preg_replace('/_{2,}/', '_', $value);
    }

    public static function asContaoContentElementTemplateName(string $value, $prefix = 'ce_'): string
    {
        $value = self::asContaoContentElementType($value);
        $value = self::addPrefix($value, $prefix);

        return preg_replace('/_{2,}/', '_', $value);
    }

    /**
     * Returns the twig namespace: e.g. @MarkocupicContaoBundleCreator.
     */
    public static function asTwigNamespace(string $vendorName, string $repositoryName): string
    {
        return preg_replace(
            '/Bundle$/',
            '',
            '@'.self::asClassName($vendorName).self::asClassName($repositoryName)
        );
    }

    /**
     * Generate phpdoc header comment from string.
     */
    public static function generateHeaderCommentFromString(string $value): string
    {
        $lines = explode("\n", $value);
        $lines = array_map(function ($line) {
            return ' * '.$line;
        }, $lines);

        return sprintf('%s%s%s', '/*'."\n", implode("\n", $lines), "\n".' */'."\n");
    }

    /**
     * Converts string into session attribute name f.eg markocupic_my_bundle_attribute.
     */
    public static function asSessionAttributeName(string $value): string
    {
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]/i', '_', $value);
        $value = preg_replace('/_{2,}/', '_', $value);
        $value = preg_replace('/^_{1}/', '', $value);

        return preg_replace('/_{1}$/', '', $value);
    }
}
