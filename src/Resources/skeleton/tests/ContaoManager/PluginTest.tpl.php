<?= "<?php\n" ?>

<?= $this->phpdoc ?>
declare(strict_types=1);

namespace <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\Tests\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\TestCase\ContaoTestCase;
use <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\ContaoManager\Plugin;
use <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\<?= $this->toplevelnamespace ?><?= $this->sublevelnamespace ?>;

/**
 * Class PluginTest
 *
 * @package <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\Tests\ContaoManager
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
        $this->assertSame(<?= $this->toplevelnamespace ?><?= $this->sublevelnamespace ?>::class, $bundles[0]->getName());
        $this->assertSame([ContaoCoreBundle::class], $bundles[0]->getLoadAfter());
    }

}
