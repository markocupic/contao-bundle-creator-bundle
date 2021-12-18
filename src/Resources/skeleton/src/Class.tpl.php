<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

namespace <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>;

<?php if ($this->addSessionAttribute) { ?>
use <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\DependencyInjection\Compiler\AddSessionBagsPass;
<?php } ?>
<?php if ($this->addFriendlyConfiguration) { ?>
use <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\DependencyInjection\<?= $this->dependencyinjectionextensionclassname; ?>;
<?php } ?>
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class <?= $this->toplevelnamespace; ?><?= $this->sublevelnamespace; ?><?= "\n"; ?>
 */
class <?= $this->toplevelnamespace; ?><?= $this->sublevelnamespace; ?> extends Bundle
{
<?php if ($this->addFriendlyConfiguration) { ?>
	public function getContainerExtension(): <?= $this->dependencyinjectionextensionclassname; ?><?= "\n"; ?>
	{
		return new <?= $this->dependencyinjectionextensionclassname; ?>();
	}

<?php } ?>
	/**
	 * {@inheritdoc}
	 */
	public function build(ContainerBuilder $container): void
	{
		parent::build($container);
		<?php if ($this->addSessionAttribute) { ?><?= "\n"; ?>
		$container->addCompilerPass(new AddSessionBagsPass());
		<?php } ?><?= "\n"; ?>
	}
}
