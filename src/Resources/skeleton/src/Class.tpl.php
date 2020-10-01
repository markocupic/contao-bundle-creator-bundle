<?= "<?php\n" ?>

declare(strict_types=1);

<?= $this->phpdoc ?>

namespace <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class <?= $this->toplevelnamespace ?><?= $this->sublevelnamespace ?><?= "\n" ?>
 */
class <?= $this->toplevelnamespace ?><?= $this->sublevelnamespace ?> extends Bundle
{
}
