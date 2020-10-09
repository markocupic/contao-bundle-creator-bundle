:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
:: src
vendor\bin\ecs check vendor/markocupic/contao-bundle-creator-bundle/src --config vendor/markocupic/contao-bundle-creator-bundle/.ecs/config/default.php
:: tests
vendor\bin\ecs check vendor/markocupic/contao-bundle-creator-bundle/tests --config vendor/markocupic/contao-bundle-creator-bundle/.ecs/config/default.php
::
cd vendor/markocupic/contao-bundle-creator-bundle/.ecs./batch/check
