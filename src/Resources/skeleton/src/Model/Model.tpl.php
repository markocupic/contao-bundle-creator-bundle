<?= "<?php\n" ?>

<?= $this->phpdoc ?>
declare(strict_types=1);

namespace <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\Model;

use Contao\Model;

/**
 * Class <?= $this->modelclassname ?><?= "\n" ?>
 *
 * @package <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\Model
 */
class <?= $this->modelclassname ?> extends Model
{
    protected static $strTable = '<?= $this->dcatable ?>';

}

