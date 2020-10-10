<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage;

/**
 * Class TagStorage.
 */
class TagStorage
{
    /**
     * @var array
     */
    private $arrTags = [];

    public function set(string $strKey, string $strValue): void
    {
        $this->arrTags[$strKey] = $strValue;
    }

    /**
     * @throws \Exception
     */
    public function get(string $strKey): string
    {
        if (!\array_key_exists($strKey, $this->arrTags)) {
            throw new \Exception(sprintf('Tag "%s" not found.', $strKey));
        }

        return $this->arrTags[$strKey];
    }

    public function getAll(): array
    {
        return $this->arrTags;
    }

    public function has(string $strKey): bool
    {
        if (\array_key_exists($strKey, $this->arrTags)) {
            return true;
        }

        return false;
    }

    public function remove(string $strKey): void
    {
        if (\array_key_exists($strKey, $this->arrTags)) {
            unset($this->arrTags[$strKey]);
        }
    }

    /**
     * Remove all tags.
     */
    public function removeAll(): void
    {
        $this->arrTags = [];
    }
}
