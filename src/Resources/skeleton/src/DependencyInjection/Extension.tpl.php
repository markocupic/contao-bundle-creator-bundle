<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

namespace <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
<?php if ($this->addFriendlyConfiguration) { ?>
use <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\DependencyInjection\Configuration;
<?php } ?>

class <?= $this->dependencyinjectionextensionclassname; ?> extends Extension
{
<?php if ($this->addFriendlyConfiguration) { ?><?= "\n"; ?>
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
<?php if ($this->addFriendlyConfiguration) { ?><?= "\n"; ?>
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);
<?php } ?>

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('parameters.yml');
        $loader->load('services.yml');
        $loader->load('listener.yml');

<?php if ($this->addFriendlyConfiguration) { ?><?= "\n"; ?>
        $rootKey = $this->getAlias();

        $container->setParameter($rootKey.'.foo.bar', $config['foo']['bar']);
<?php } ?>
    }
}
