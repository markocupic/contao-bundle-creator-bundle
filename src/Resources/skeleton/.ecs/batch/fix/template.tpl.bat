:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
:: templates
vendor\bin\ecs check vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/src/Resources/contao/templates --fix --config vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/.ecs/config/template.php
::
cd vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/.ecs./batch/fix
