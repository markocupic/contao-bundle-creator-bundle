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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\SanitizeInput;

/**
 * Class SanitizeInput
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker\SanitizeInput
 */
class SanitizeInput
{

    /**
     * Converts a string to namespace format
     * "my_custom name-space" becomes "MyCustomNameSpace"
     * "foo_Bar_fooBar99" becomes "FooBarFooBar99"
     *
     * @param string $str
     * @return string
     */
    public function toPsr4Namespace(string $str): string
    {
        $str = str_replace('/[^A-Za-z0-9_\-]/', '', $str);
        $str = str_replace('-', '_', $str);
        $str = str_replace(' ', '_', $str);

        // Split where Uppercase letter begins fooBar -> foo_Bar
        $pieces = preg_split('/(?=[A-Z\s]{1,})/', $str);
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
        $strBundleNamespace = implode('', $arrNamespace);

        return $strBundleNamespace;
    }

    /**
     * Converts a string to snakecase
     * My custom module => my_custom_module
     *
     * @param string $str
     * @return string
     */
    public function toSnakecase(string $str): string
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
     * Get the frontend module type (f.ex. my_custom_module)
     * Convention => snakecase with postfix "_module"
     *
     * @param string $str (requires tl_contao_bundle_creator.frontendmoduletype)
     * @param string $postfix
     * @return string
     */
    public function getSanitizedFrontendModuleType(string $str, $postfix = '_module'): string
    {
        $str = $this->toSnakecase((string) $str);

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
     * Get the backend module type (f.ex. my_custom_module)
     * Convention => snakecase
     *
     * @return string
     */

    /**
     * Get the backend module type (f.ex. my_custom_module)
     * Convention => snakecase
     *
     * @param string $str (requires tl_contao_bundle_creator.backendmoduletype)
     * @return string
     */
    public function getSanitizedBackendModuleType(string $str): string
    {
        $str = $this->toSnakecase($str);
        return $str;
    }

    /**
     * Get the sanitized dca tablename f.ex. tl_sample_table
     *
     * @param string $str (requires tl_contao_bundle_creator.dcatable)
     * @return string
     * @throws \Exception
     */
    public function getSanitizedDcaTableName(string $str): string
    {
        if (!strlen((string) $str))
        {
            throw new \Exception('No dca tablename set.');
        }

        $str = strtolower($str);
        $str = preg_replace('/\-|\s/', '_', $str);
        $str = preg_replace('/_{2,}/', '_', $str);
        $str = preg_replace('/[^A-Za-z0-9_]|_$/', '', $str);
        if (!preg_match('/^tl_/', $str))
        {
            $str = 'tl_' . $str;
        }
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
    public function getSanitizedFrontendModuleClassname(string $str, string $postfix = 'Controller'): string
    {
        $str = $this->getSanitizedFrontendModuleType($str);
        $str = $this->toPsr4Namespace($str);
        return $str . $postfix;
    }

    /**
     * Get model classname f.ex. SampleTableModel
     *
     * @param string $str (requires tl_contao_bundle_creator.dcatable)
     * @param string $postfix
     * @return string
     * @throws \Exception
     */
    public function getSanitizedModelClassname(string $str, string $postfix = 'Model'): string
    {
        $str = $this->getSanitizedDcaTableName($str);
        $str = preg_replace('/^tl_/', '', $str);
        $str = $this->toPsr4Namespace($str);
        return $str . $postfix;
    }

    /**
     * Get the frontend module template name from the frontend module type and add the prefix "mod_"
     *
     * @param string $strPrefix
     * @return string $str (requires tl_contao_bundle_creator.frontendmoduletype)
     * @return string
     */
    public function getSanitizedFrontendModuleTemplateName(string $str, $strPrefix = 'mod_'): string
    {
        $str = $this->getSanitizedFrontendModuleType($str);
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
