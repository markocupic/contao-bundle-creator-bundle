<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao Bundle Creator
 * @license    MIT
 * @see        https://github.com/markocupic/contao-bundle-creator-bundle
 *
 */

/**
 * Operations
 */
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['edit'] = ["Bundle ID %s bearbeiten", "Bundle ID %s bearbeiten"];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['copy'] = ["Bundle ID %s duplizieren", "Bundle ID %s duplizieren"];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['delete'] = ["Bundle ID %s löschen", "Bundle ID %s löschen"];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['show'] = ["Bundle ID %s ansehen", "Details des Bundles ID %s anzeigen"];

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['bundle_settings_legend'] = "Bundle Einstellungen";
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['composer_settings_legend'] = "composer.json Einstellungen";
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['rootcomposer_settings_legend'] = "(ROOT-)composer.json Einstellungen";
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['dcatable_settings_legend'] = "DCA Tabellen Einstellungen";
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['frontendmodule_settings_legend'] = "Frontendmodul Einstellungen";
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['custom_route_settings_legend'] = "Custom Route Einstellungen";

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['bundlename'] = ["Bundle Name", "Geben Sie einen Namen für das Bundle ein."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['vendorname'] = ["Vendorname", "Geben Sie Ihren Vendornamen ein."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['repositoryname'] = ["(Github-) Repository Name (mit \"contao-\" als Prefix und \"-bundle\" als Suffix)", "Geben Sie den Repository Namen für das Bundle ein."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['overwriteexisting'] = ["Gleichnamiges Bundle überschreiben", "Soll ein gleichnamiges, bereits bestehendes Bundle überschrieben werden?"];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['composerdescription'] = ["composer.json: Beschreibungstext", "Geben Sie den Beschreibungstext für die composer.json ein."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['composerpackageversion'] = ["composer.json: Paketversion (Nötig für Paketupload mit Contao Manager)", "Geben Sie die Paketversion im Format 1.x ein. (Nur nötig wenn bundle über Paketupload in Contao Manager bereitgestellt wird)"];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['rootcomposerextendrepositorieskey'] = ["composer.json (ROOT): Den Repositories-Key erweitern?", "Soll der Repositories-Key in der composer.json im ROOT-Verzeichnis erweitert werden?"];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['rootcomposerextendrequirekey'] = ["composer.json (ROOT): Den Require-Key erweitern?", "Siehe: <a href=\"https://getcomposer.org/doc/05-repositories.md#path\">path</a> & <a href=\"https://getcomposer.org/doc/05-repositories.md#vcs\">vcs</a>"];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['composerlicense'] = ["composer.json: Lizenz", "Geben Sie die Lizenz an. z.B. MIT."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['composerauthorname'] = ["composer.json: Name des Autors", "Geben Sie den Namen des Entwicklers ein."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['composerauthoremail'] = ["composer.json: E-Mail-Adresse des Autors", "Geben Sie die E-Mail-Adresse des Autors an."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['composerauthorwebsite'] = ["composer.json: Webseite", "Geben Sie die Webseite des Autors an. z.B. https://github.com/vendorname"];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['addBackendModule'] = ["Backendmodul mit DCA Tabelle hinzufügen", "Geben Sie an, ob ein Backendmodul mit DCA Tabelle generiert werden soll."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['backendmodulecategory'] = ["Backendmodul-Kategorie", "Geben Sie die Backenmodul-Kategorie in snakecase an."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['backendmodulecategorytrans'] = ["Backendmodul-Kategorie-Übersetzung", "Falls die Kategorie bereits besteht, kann das Feld leer gelassen werden."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['backendmoduletype'] = ["Backendmodul-Typ", "Geben Sie den Type des Modules in snakecase an."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['backendmoduletrans'] = ["Backendmodul-Typ-Übersetzung und Beschreibung", "Geben Sie dem Modul einen Namen und eine Beschreibung."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['dcatable'] = ["DCA Tabellenname", "Geben Sie den Namen der Tabelle an: z.B. tl_my_pets"];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['addFrontendModule'] = ["Frontendmodul hinzufügen", "Fügen Sie dem Package ein Frontendmodul hinzu."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['frontendmodulecategory'] = ["Frontendmodul-Kategorie (snakecase)", "Geben Sie die Frontendmodul-Kategorie in camelcase an."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['frontendmodulecategorytrans'] = ["Frontendmodul-Kategorie-Übersetzung", "Falls die Kategorie bereits besteht, kann das Feld leer gelassen werden."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['frontendmoduletype'] = ["Frontendmodul-Typ (snakecase mit \"_module\" als suffix)", "Geben Sie den Typ des Modules in snakecase an."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['frontendmoduletrans'] = ["Frontendmodul-Typ-Übersetzung und Beschreibung", "Geben Sie dem Modul einen Namen und eine Beschreibung."];
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['addCustomRoute'] = ["Eine \"custom route\" hinzufügen", "Geben Sie an, ob Sie eine \"custom route\" hinzufügen möchten."];

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['createBundleButton'] = "Bundle-Generator starten";
$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['downloadBundleButton'] = "Bundle herunterladen";

