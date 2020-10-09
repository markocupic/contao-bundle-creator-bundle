:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
vendor\bin\ecs check vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/tests --fix --config vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/.ecs/config/default.php
cd vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/.ecs./batch/fix
