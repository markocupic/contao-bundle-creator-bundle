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

namespace Markocupic\ContaoBundleCreatorBundle\Listener\ContaoHook;

use Contao\Widget;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Str\Str;

/**
 * Class AddCustomRegexpListener.
 */
class AddCustomRegexpListener
{
    /**
     * @param $input
     *
     * @throws \Exception
     */
    public function cbcbRegexp(string $regexp, $input, Widget $widget): bool
    {
        if (preg_match('/^cbcb_(.*)$/', $regexp, $matches)) {
            if (isset($matches[1]) && $widget->name === $matches[1]) {
                $blnTested = false;
                $fittedInput = $input;

                switch ($matches[1]) {
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

                    case 'backendmodulecategory':
                    case 'frontendmodulecategory':
                    case 'contentelementcategory':
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

                    case 'contentelementtype':
                        $blnTested = true;
                        $fittedInput = Str::asContaoContentElementType($input);
                        break;
                }

                if ($blnTested && $fittedInput !== $input) {
                    $widget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['cbcb_rgxp'], $fittedInput, $input));

                    return true;
                }
            }
        }

        return false;
    }
}
