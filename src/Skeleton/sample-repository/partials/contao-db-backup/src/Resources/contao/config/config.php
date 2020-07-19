<?php

/**
 * Contao Db Backup
 *
 * Copyright (C) 2019 Marko Cupic
 *
 * @package contao-db-backup
 * @link    https://github.com/markocupic/contao-db-backup
 * @license MIT
 */

// Keep backup files for 30 days on the server
$GLOBALS['TL_CONFIG']['contaoDbBackupKeepBackupFiles'] = 30;

// TL_CRON
$GLOBALS['TL_CRON']['daily']['doContaoDbBackup'] = array('ContaoDbBackup\ContaoDbBackup', 'doDbBackup');

