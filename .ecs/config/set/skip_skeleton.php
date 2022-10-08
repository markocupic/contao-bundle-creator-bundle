<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;

return function (ECSConfig $ECSConfig): void {
    $parameters = $ECSConfig->parameters();

    $parameters->set(Option::SKIP, [
        '*/Resources/contao/skeleton',
    ]);
};
