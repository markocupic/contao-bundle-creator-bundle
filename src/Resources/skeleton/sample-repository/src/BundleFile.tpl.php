<?= "<?php\n" ?>

<?= $this->phpdoc ?>
declare(strict_types=1);

namespace <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class <?= $this->toplevelnamespace ?><?= $this->sublevelnamespace ?><?= "\n" ?>
 *
 * @package <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?><?= "\n" ?>
 */
class <?= $this->toplevelnamespace ?><?= $this->sublevelnamespace ?> extends Bundle
{
}
