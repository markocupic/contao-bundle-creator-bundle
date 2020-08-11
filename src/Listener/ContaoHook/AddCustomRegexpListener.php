<?php

/**
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 *
 * @author     Marko Cupic
 * @package    Contao Bundle Creator
 * @license    MIT
 * @see        https://github.com/markocupic/contao-bundle-creator-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoBundleCreatorBundle\Listener\ContaoHook;

use Contao\Widget;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;

/**
 * Class AddCustomRegexpListener
 *
 * @package Markocupic\ContaoBundleCreatorBundle\Listener\ContaoHook
 */
class AddCustomRegexpListener
{

    /**
     * @param string $regexp
     * @param $input
     * @param Widget $widget
     * @return bool
     * @throws \Exception
     */
    public function cbcbRegexp(string $regexp, $input, Widget $widget): bool
    {

        if (preg_match('/^cbcb_(.*)$/', $regexp, $matches))
        {
            if (isset($matches[1]) && $widget->name === $matches[1])
            {
                $blnTested = false;
                $fittedInput = $input;
                switch ($matches[1])
                {
                    case 'vendorname':
                        $blnTested = true;
                        $fittedInput = Str::asVendorName($input);
                        break;
                    case 'repositoryname':
                        $blnTested = true;
                        $fittedInput = Str::asRepositoryName($input);
                        break;
                    case 'dcatable':
                        $blnTested = true;
                        $fittedInput = Str::asContaoDcaTable($input);
                        break;
                    case 'frontendmodulecategory':
                    case 'backendmodulecategory':
                        $blnTested = true;
                        $fittedInput = Str::asSnakeCase($input);
                        break;
                    case 'backendmoduletype':
                        $blnTested = true;
                        $fittedInput = Str::asContaoBackendModuleType($input);
                        break;
                    case 'frontendmoduletype':
                        $blnTested = true;
                        $fittedInput = Str::asContaoFrontendModuleType($input);
                        break;
                }

                if ($blnTested && $fittedInput !== $input)
                {
                    $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['cbcb_rgxp'], $fittedInput, $input));
                    return true;
                }
            }
        }

        return false;
    }
}
