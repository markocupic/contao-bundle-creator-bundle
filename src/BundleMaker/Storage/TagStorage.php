<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage;

class TagStorage
{
    private array $tags = [];

    public function set(string $key, string $value): void
    {
        $this->tags[$key] = $value;
    }

    /**
     * @throws \Exception
     */
    public function get(string $key): string
    {
        if (!\array_key_exists($key, $this->tags)) {
            throw new \Exception(\sprintf('Tag "%s" not found.', $key));
        }

        return $this->tags[$key];
    }

    public function getAll(): array
    {
        return $this->tags;
    }

    public function has(string $key): bool
    {
        if (\array_key_exists($key, $this->tags)) {
            return true;
        }

        return false;
    }

    public function remove(string $key): void
    {
        if (\array_key_exists($key, $this->tags)) {
            unset($this->tags[$key]);
        }
    }

    /**
     * Remove all tags.
     */
    public function removeAll(): void
    {
        $this->tags = [];
    }
}
