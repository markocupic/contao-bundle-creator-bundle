<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_contao_bundle_creator'] = [
    'config'      => [
        'dataContainer'    => DC_Table::class,
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list'        => [
        'sorting'           => [
            'mode'        => DataContainer::MODE_SORTABLE,
            'fields'      => ['bundlename'],
            'flag'        => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label'             => [
            'fields' => ['bundlename'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy'   => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['editRootComposer', 'addBackendModule', 'addFrontendModule', 'addContentElement'],
        'default'      => '
		    {bundle_settings_legend},bundlename,vendorname,repositoryname,overwriteexisting;
            {composer_settings_legend},composerdescription,composerlicense,composerauthorname,composerauthoremail,composerauthorwebsite,composerpackageversion;
            {rootcomposer_settings_legend},editRootComposer;
            {dcatable_settings_legend},addBackendModule;
            {frontendmodule_settings_legend},addFrontendModule;
            {contentelement_settings_legend},addContentElement;
            {custom_route_settings_legend},addCustomRoute;
            {custom_session_attribute_settings_legend},addSessionAttribute;
            {friendly_configuration_settings_legend},addFriendlyConfiguration;
            {coding_style_legend},addEasyCodingStandard
            ',
    ],
    'subpalettes' => [
        'editRootComposer'  => 'rootcomposerextendrepositorieskey',
        'addBackendModule'  => 'dcatable,backendmodulecategory,backendmodulecategorytrans,backendmoduletype,backendmoduletrans',
        'addFrontendModule' => 'frontendmodulecategory,frontendmodulecategorytrans,frontendmoduletype,frontendmoduletrans',
        'addContentElement' => 'contentelementcategory,contentelementcategorytrans,contentelementtype,contentelementtrans',
    ],
    'fields'      => [
        'id'                                => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp'                            => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'bundlename'                        => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'vendorname'                        => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_vendorname'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'repositoryname'                    => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'doNotCopy' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_repositoryname', 'placeholder' => 'e.g. contao-pet-collection-bundle'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'overwriteexisting'                 => [
            'inputType' => 'checkbox',
            'exclude'   => true,
            'sorting'   => true,
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'composerdescription'               => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'cbcb_composerdescription'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'composerpackageversion'            => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => false, 'maxlength' => 16, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(16) NOT NULL default ''",
        ],
        'composerlicense'                   => [
            'inputType' => 'select',
            'exclude'   => true,
            'sorting'   => true,
            'default'   => 'GPL-3.0-or-later',
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'composerauthorname'                => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alpha'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'composerauthoremail'               => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'email'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'composerauthorwebsite'             => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'httpurl'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'editRootComposer'                  => [
            'inputType' => 'checkbox',
            'exclude'   => true,
            'sorting'   => true,
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'rootcomposerextendrepositorieskey' => [
            'inputType' => 'select',
            'exclude'   => true,
            'options'   => ['path', 'vcs-github'],
            'sorting'   => true,
            'eval'      => ['includeBlankOption' => false, 'tl_class' => 'clr'],
            'sql'       => "varchar(16) NOT NULL default ''",
        ],
        'addBackendModule'                  => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'dcatable'                          => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_dcatable', 'placeholder' => 'e.g. tl_pets'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'backendmodulecategory'             => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_backendmodulecategory', 'placeholder' => 'e.g. pet_modules'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'backendmodulecategorytrans'        => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'backendmoduletype'                 => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_backendmoduletype', 'placeholder' => 'e.g. pet_collection'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'backendmoduletrans'                => [
            'inputType' => 'text',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'eval'      => ['mandatory' => true, 'multiple' => true, 'size' => 2, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'addFrontendModule'                 => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'frontendmodulecategory'            => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_frontendmodulecategory', 'placeholder' => 'e.g. pet_modules'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'frontendmodulecategorytrans'       => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'frontendmoduletype'                => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_frontendmoduletype', 'placeholder' => 'e.g. pet_listing'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'frontendmoduletrans'               => [
            'inputType' => 'text',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'eval'      => ['mandatory' => true, 'multiple' => true, 'size' => 2, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'addContentElement'                 => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'contentelementcategory'            => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_contentelementcategory', 'placeholder' => 'e.g. pet_elements'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'contentelementcategorytrans'       => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'contentelementtype'                => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => DataContainer::SORT_INITIAL_LETTER_ASC,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_contentelementtype', 'placeholder' => 'e.g. pet_listing'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'contentelementtrans'               => [
            'inputType' => 'text',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'eval'      => ['mandatory' => true, 'multiple' => true, 'size' => 2, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'addCustomRoute'                    => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'addEasyCodingStandard'             => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'addSessionAttribute'               => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'addFriendlyConfiguration'          => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
    ],
];
