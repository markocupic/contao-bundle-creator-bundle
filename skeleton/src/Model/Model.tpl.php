<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

namespace <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\Model;

use Contao\Model;

class <?= $this->modelclassname; ?> extends Model
{
    protected static $strTable = '<?= $this->dcatable; ?>';
}
