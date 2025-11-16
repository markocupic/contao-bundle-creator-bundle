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

namespace Markocupic\ContaoBundleCreatorBundle;

use Symfony\Component\Filesystem\Path;

class Skeleton
{
    private static string|null $customSkeletonPath;

    private static string|null $defaultSkeletonPath;

    public static function getDefaultPath(): string
    {
        return static::$defaultSkeletonPath ?? Path::normalize(Path::join(__DIR__, '/../skeleton'));
    }

    public static function getCustomTemplatePath(): string
    {
        return static::$customSkeletonPath ?? Path::normalize(Path::join(__DIR__, '/../../../../templates/contao-bundle-creator-bundle/skeleton'));
    }

    public static function setCustomTemplatePath(string $customTemplatePath): void
    {
        static::$customSkeletonPath = $customTemplatePath;
    }

    public static function setDefaultPath(string $defaultSkeletonPath): void
    {
        static::$defaultSkeletonPath = $defaultSkeletonPath;
    }
}
