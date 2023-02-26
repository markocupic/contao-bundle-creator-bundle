:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
php vendor\bin\ecs check vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/src --fix --config vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/tools/ecs/config.php
php vendor\bin\ecs check vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/contao --fix --config vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/tools/ecs/config.php
php vendor\bin\ecs check vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/config --fix --config vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/tools/ecs/config.php
php vendor\bin\ecs check vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/templates --fix --config vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/tools/ecs/config.php
php vendor\bin\ecs check vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/tests --fix --config vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/tools/ecs/config.php
