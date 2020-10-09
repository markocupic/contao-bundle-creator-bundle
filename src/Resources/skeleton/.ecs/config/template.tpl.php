<?= "<?php\n" ?>

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__.'/../../../../contao/easy-coding-standard/config/template.php');
    $containerConfigurator->import(__DIR__.'/set/header_comment_fixer.php');
};
