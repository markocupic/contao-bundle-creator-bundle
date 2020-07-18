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

namespace Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Utils;

/**
 * Class Tags
 * @package Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Utils
 */
class Tags
{

    /** @var array */
    private $arrTags = [];

    /**
     * @param string $strKey
     * @param string $strValue
     * @throws \Exception
     */
    public function add(string $strKey, string $strValue)
    {
        if (in_array($strKey, $this->arrTags))
        {
            throw new \Exception(sprintf('Tag "%s" has already been set and can not be overwritten.'));
        }
        $this->arrTags[$strKey] = $strValue;
    }

    /**
     * @param string $strKey
     * @return bool
     */
    public function has(string $strKey): bool
    {
        if (in_array($strKey, $this->arrTags))
        {
            return true;
        }
        return false;
    }

    /**
     * @param string $strKey
     */
    public function remove(string $strKey): void
    {
        if (isset($this->arrStorrage[$strKey]))
        {
            unset($this->arrTags[$strKey]);
        }
    }

    /**
     * Remove all tags
     */
    public function removeAll(): void
    {
        $this->arrStorrage = [];
    }

    /**
     * @param string $strKey
     * @return string
     * @throws \Exception
     */
    public function get(string $strKey): string
    {
        if (!in_array($strKey, $this->arrTags))
        {
            throw new \Exception(sprintf('Tag "%s" not found.'));
        }
        return $this->arrTags[$strKey];
    }

    /**
     * @param string $strKey
     * @return string
     * @throws \Exception
     */
    public function getAll(): array
    {
        return $this->arrTags;
    }

}
