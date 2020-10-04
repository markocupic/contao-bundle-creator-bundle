:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
:: src
vendor\bin\ecs check vendor/markocupic/contao-bundle-creator-bundle/src --fix --config vendor/markocupic/contao-bundle-creator-bundle/.ecs/config/default.yaml
:: tests
vendor\bin\ecs check vendor/markocupic/contao-bundle-creator-bundle/tests --fix --config vendor/markocupic/contao-bundle-creator-bundle/.ecs/config/default.yaml
:: legacy
vendor\bin\ecs check vendor/markocupic/contao-bundle-creator-bundle/src/Resources/contao --fix --config vendor/markocupic/contao-bundle-creator-bundle/.ecs/config/legacy.yaml
:: templates
vendor\bin\ecs check vendor/markocupic/contao-bundle-creator-bundle/src/Resources/contao/templates --fix --config vendor/markocupic/contao-bundle-creator-bundle/.ecs/config/template.yaml
::
cd vendor/markocupic/contao-bundle-creator-bundle/.ecs./batch/fix
