<?= "<?php\n" ?>

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;

return static function (ECSConfig $ECSConfig): void {

    $services = $ECSConfig->services();

    $services
        ->set(HeaderCommentFixer::class)
        ->call('configure', [[
            'header' => "<?= $this->ecsphpdoc ?>",
        ]])
    ;
};