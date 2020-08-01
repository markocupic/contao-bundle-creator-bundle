<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Mein Steckbrief
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-steckbrief-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoBundleMakerBundle\Tests\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\ContaoManager\Plugin;
use Markocupic\ContaoBundleCreatorBundle\MarkocupicContaoBundleCreatorBundle;

/**
 * Class PluginTest
 *
 * @package Markocupic\ContaoBundleMakerBundle\Tests\ContaoManager
 */
class PluginTest extends ContaoTestCase
{
    /**
     * Test Contao manager plugin class instantiation
     */
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(Plugin::class, new Plugin());
    }

    /**
     * Test returns the bundles
     */
    public function testGetBundles(): void
    {
        $plugin = new Plugin();

        /** @var array $bundles */
        $bundles = $plugin->getBundles(new DelegatingParser());

        $this->assertCount(1, $bundles);
        $this->assertInstanceOf(BundleConfig::class, $bundles[0]);
        $this->assertSame(MarkocupicContaoBundleCreatorBundle::class, $bundles[0]->getName());
        $this->assertSame([ContaoCoreBundle::class], $bundles[0]->getLoadAfter());
    }

}
