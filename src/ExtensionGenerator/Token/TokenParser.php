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

namespace Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Token;

use Contao\System;
use Psr\Log\LogLevel;

/**
 * Class TagStorage
 * @package Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Storage
 */
class TokenParser
{

    /**
     * Parse simple tokens extended from \Contao\StringUtil::parseSimpleTokens()
     * @author Marko Cupic, 23.07.2020
     * Sole modification: line 165
     * Original pattern moved from: '/({[^{}]+})\n?/'
     * To: '/({if [^{}]+}|{elseif [^{}]+}|{else}|{endif})\n?/'
     *
     * @param string $strString The string to be parsed
     * @param array $arrData The replacement data
     *
     * @return string The converted string
     *
     * @throws \Exception                If $strString cannot be parsed
     * @throws \InvalidArgumentException If there are incorrectly formatted if-tags
     */
    public static function parseSimpleTokens($strString, $arrData)
    {
        $strReturn = '';
        $replaceTokens = static function ($strSubject) use ($arrData) {
            // Replace tokens
            return preg_replace_callback
            (
                '/##([^=!<>\s]+?)##/',
                static function (array $matches) use ($arrData) {
                    if (!\array_key_exists($matches[1], $arrData))
                    {
                        System::getContainer()
                            ->get('monolog.logger.contao')
                            ->log(LogLevel::INFO, sprintf('Tried to parse unknown simple token "%s".', $matches[1]));

                        return '##' . $matches[1] . '##';
                    }

                    return $arrData[$matches[1]];
                },
                $strSubject
            );
        };

        $evaluateExpression = static function ($strExpression) use ($arrData) {
            if (!preg_match('/^([^=!<>\s]+) *([=!<>]+)(.+)$/s', $strExpression, $arrMatches))
            {
                return false;
            }

            $strToken = $arrMatches[1];
            $strOperator = $arrMatches[2];
            $strValue = trim($arrMatches[3], ' ');

            if (!\array_key_exists($strToken, $arrData))
            {
                System::getContainer()
                    ->get('monolog.logger.contao')
                    ->log(LogLevel::INFO, sprintf('Tried to evaluate unknown simple token "%s".', $strToken));

                return false;
            }

            $varTokenValue = $arrData[$strToken];

            if (is_numeric($strValue))
            {
                if (strpos($strValue, '.') === false)
                {
                    $varValue = (int) $strValue;
                }
                else
                {
                    $varValue = (float) $strValue;
                }
            }
            elseif (strtolower($strValue) === 'true')
            {
                $varValue = true;
            }
            elseif (strtolower($strValue) === 'false')
            {
                $varValue = false;
            }
            elseif (strtolower($strValue) === 'null')
            {
                $varValue = null;
            }
            elseif (0 === strncmp($strValue, '"', 1) && substr($strValue, -1) === '"')
            {
                $varValue = str_replace('\"', '"', substr($strValue, 1, -1));
            }
            elseif (0 === strncmp($strValue, "'", 1) && substr($strValue, -1) === "'")
            {
                $varValue = str_replace("\\'", "'", substr($strValue, 1, -1));
            }
            else
            {
                throw new \InvalidArgumentException(sprintf('Unknown data type of comparison value "%s".', $strValue));
            }

            switch ($strOperator)
            {
                case '==':
                    return $varTokenValue == $varValue;

                case '!=':
                    return $varTokenValue != $varValue;

                case '===':
                    return $varTokenValue === $varValue;

                case '!==':
                    return $varTokenValue !== $varValue;

                case '<':
                    return $varTokenValue < $varValue;

                case '>':
                    return $varTokenValue > $varValue;

                case '<=':
                    return $varTokenValue <= $varValue;

                case '>=':
                    return $varTokenValue >= $varValue;

                default:
                    throw new \InvalidArgumentException(sprintf('Unknown simple token comparison operator "%s".', $strOperator));
            }
        };

        // The last item is true if it is inside a matching if-tag
        $arrStack = [true];

        // The last item is true if any if/elseif at that level was true
        $arrIfStack = [true];

        /**
         * @author Marko Cupic, 23.07.2020
         * Modified line 165 from \Contao\StringUtil()
         * Original pattern: '/({[^{}]+})\n?/'
         * New preg_splt pattern: '/({if [^{}]+}|{elseif [^{}]+}|{else}|{endif})\n?/'
         */
        $arrTags = preg_split('/({if [^{}]+}|{elseif [^{}]+}|{else}|{endif})\n?/', $strString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        // Parse the tokens
        // Parse the tokens
        foreach ($arrTags as $strTag)
        {
            // True if it is inside a matching if-tag
            $blnCurrent = $arrStack[\count($arrStack) - 1];
            $blnCurrentIf = $arrIfStack[\count($arrIfStack) - 1];

            if (strncmp($strTag, '{if ', 4) === 0)
            {
                $blnExpression = $evaluateExpression(substr($strTag, 4, -1));
                $arrStack[] = $blnCurrent && $blnExpression;
                $arrIfStack[] = $blnExpression;
            }
            elseif (strncmp($strTag, '{elseif ', 8) === 0)
            {
                $blnExpression = $evaluateExpression(substr($strTag, 8, -1));
                array_pop($arrStack);
                array_pop($arrIfStack);
                $arrStack[] = !$blnCurrentIf && $arrStack[\count($arrStack) - 1] && $blnExpression;
                $arrIfStack[] = $blnCurrentIf || $blnExpression;
            }
            elseif (strncmp($strTag, '{else}', 6) === 0)
            {
                array_pop($arrStack);
                array_pop($arrIfStack);
                $arrStack[] = !$blnCurrentIf && $arrStack[\count($arrStack) - 1];
                $arrIfStack[] = true;
            }
            elseif (strncmp($strTag, '{endif}', 7) === 0)
            {
                array_pop($arrStack);
                array_pop($arrIfStack);
            }
            elseif ($blnCurrent)
            {
                $strReturn .= $replaceTokens($strTag);
            }
        }

        // Throw an exception if there is an error
        if (\count($arrStack) !== 1)
        {
            throw new \Exception('Error parsing simple tokens');
        }

        return $strReturn;
    }

}
