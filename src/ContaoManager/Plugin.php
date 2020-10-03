<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

/**
 * Class Plugin.
 */
class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create('Markocupic\ContaoBundleCreatorBundle\MarkocupicContaoBundleCreatorBundle')
                ->setLoadAfter(['Contao\CoreBundle\ContaoCoreBundle']),
        ];
    }
}
