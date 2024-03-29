<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

namespace <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class <?= $this->dependencyinjectionextensionclassname; ?> extends Extension
{
<?php if ($this->addFriendlyConfiguration) { ?>
    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return Configuration::ROOT_KEY;
    }

<?php } ?>
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
<?php if ($this->addFriendlyConfiguration) { ?>
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);
<?php } ?>

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );

        $loader->load('parameters.yaml');
        $loader->load('services.yaml');
        $loader->load('listener.yaml');
<?php if ($this->addFriendlyConfiguration) { ?><?= "\n"; ?>
        $rootKey = $this->getAlias();

        $container->setParameter($rootKey.'.foo.bar', $config['foo']['bar']);
<?php } ?>
    }
}
