<?= "<?php\n" ?>

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ECSConfig): void {
    // Contao
    $ECSConfig->import(__DIR__ . '../../../../../contao/easy-coding-standard/config/contao.php');

    // Custom
    $ECSConfig->import(__DIR__.'/set/config.php');
};

