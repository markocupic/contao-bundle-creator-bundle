:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin

@echo off
:: Change to the location where the batch file itself is running:
cd /d "%~dp0"

:: Change to the bundle directory
cd..
cd..
cd..

../../../vendor\bin\ecs check src --fix --config tools/ecs/config/default.php
