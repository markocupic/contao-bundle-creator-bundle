<?= "<?php\n" ?>

declare(strict_types=1);

<?= $this->phpdoc ?>

namespace <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class <?= $this->dependencyinjectionextensionclassname ?><?= "\n" ?>
 */
class <?= $this->dependencyinjectionextensionclassname ?> extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('parameters.yml');
        $loader->load('services.yml');
        $loader->load('listener.yml');
    }
}
