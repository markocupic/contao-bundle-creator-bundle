<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

namespace <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
<?php if ($this->addCustomRoute) { ?>
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;
<?php } ?>

class Plugin implements BundlePluginInterface<?php if ($this->addCustomRoute) { ?>, RoutingPluginInterface<?php } ?><?= "\n"; ?>
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create('<?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\<?= $this->toplevelnamespace; ?><?= $this->sublevelnamespace; ?>')
                ->setLoadAfter(['Contao\CoreBundle\ContaoCoreBundle']),
        ];
    }
<?php if ($this->addCustomRoute) { ?>

    /**
     * @throws \Exception
     *
     * @return RouteCollection|null
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        return $resolver
            ->resolve(__DIR__.'/../../config/routes.yaml')
            ->load(__DIR__.'/../../config/routes.yaml')
    ;
    }
<?php } ?>
}
