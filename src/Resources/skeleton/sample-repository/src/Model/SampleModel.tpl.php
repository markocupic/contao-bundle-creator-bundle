<?= "<?php\n" ?>

<?= $phpdoc ?>
declare(strict_types=1);

namespace <?= $toplevelnamespace ?>\<?= $sublevelnamespace ?>\Model;

use Contao\Model;

/**
 * Class <?= $modelclassname ?><?= "\n" ?>
 * @package <?= $toplevelnamespace ?>\<?= $sublevelnamespace ?>\Model
 */
class <?= $modelclassname ?> extends Model
{
    protected static $strTable = '<?= $dcatable ?>';

}

