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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage;

/**
 * Class TagStorage
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage
 */
class TagStorage
{

    /** @var array */
    private $arrTags = [];

    /**
     * @param string $strKey
     * @param string $strValue
     */
    public function set(string $strKey, string $strValue)
    {
        $this->arrTags[$strKey] = $strValue;
    }

    /**
     * @param string $strKey
     * @return bool
     */
    public function has(string $strKey): bool
    {
        if (array_key_exists ($strKey, $this->arrTags))
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
        if (!array_key_exists ($strKey, $this->arrTags))
        {
            throw new \Exception(sprintf('Tag "%s" not found.', $strKey));
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
