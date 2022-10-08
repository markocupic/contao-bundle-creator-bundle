<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ECSConfig): void {
    // Contao
    $ECSConfig->import(__DIR__ . '../../../../../contao/easy-coding-standard/config/contao.php');

    // Custom
    $ECSConfig->import(__DIR__.'/set/header_comment_fixer.php');

    // Skip
    $ECSConfig->import(__DIR__.'/set/skip_dca.php');
    $ECSConfig->import(__DIR__.'/set/skip_skeleton.php');

};
