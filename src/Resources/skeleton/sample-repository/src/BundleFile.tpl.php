<?= "<?php\n" ?>

<?= $phpdoc ?>
declare(strict_types=1);

namespace <?= $toplevelnamespace ?>\<?= $sublevelnamespace ?>;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class <?= $toplevelnamespace ?><?= $sublevelnamespace ?><?= "\n" ?>
 * @package <?= $toplevelnamespace ?>\<?= $sublevelnamespace ?><?= "\n" ?>
 */
class <?= $toplevelnamespace ?><?= $sublevelnamespace ?> extends Bundle
{
}
