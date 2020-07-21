<?php

/**
 * Contao Db Backup
 *
 * Copyright (C) 2018 Marko Cupic
 *
 * @package contao-db-backup
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'ContaoDbBackup',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Src
	'ContaoDbBackup\ContaoDbBackup' => 'system/modules/contao-db-backup/src/classes/ContaoDbBackup.php',
));
