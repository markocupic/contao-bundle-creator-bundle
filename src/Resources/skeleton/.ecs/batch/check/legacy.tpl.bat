# Use this batch file in your IDE (Windows)
# In PhpStorm install the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
vendor\bin\ecs check vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/src --config vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/.ecs/config/default.yaml
cd vendor/<?= $this->vendorname ?>/<?= $this->repositoryname ?>/.ecs./batch/check
