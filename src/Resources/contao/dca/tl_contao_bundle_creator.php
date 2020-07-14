<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao Bundle Creator
 * @license    MIT
 * @see        https://github.com/markocupic/contao-bundle-creator
 *
 */

/**
 * Table tl_contao_bundle_creator
 */
$GLOBALS['TL_DCA']['tl_contao_bundle_creator'] = [

    // Config
    'config'   => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary'
            ]
        ],
    ],
    'edit'     => array(
        'buttons_callback' => array(
            array('tl_contao_bundle_creator', 'buttonsCallback')
        )
    ),
    'list'     => [
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'filter;sort,search,limit'
        ],
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show'   => [
                'label'      => &$GLOBALS['TL_LANG']['tl_contao_bundle_creator']['show'],
                'href'       => 'act=show',
                'icon'       => 'show.gif',
                'attributes' => 'style="margin-right:3px"'
            ],
        ]
    ],
    // Palettes
    'palettes' => [
        'default' => '{bundle_legend},title,vendorname,githubRepositoryName'
    ],
    // Fields
    'fields'   => [
        'id'     => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'  => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50',

            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'vendorname'  => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50',

            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'githubRepositoryName'  => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50',

            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
    ]
];

/**
 * Class tl_contao_bundle_creator
 */
class tl_contao_bundle_creator extends Contao\Backend{

    /**
     * @param $arrButtons
     * @param \Contao\DC_Table $dc
     * @return mixed
     */
    public function buttonsCallback($arrButtons, Contao\DC_Table $dc)
    {
        if (Contao\Input::get('act') === 'edit')
        {
            $arrButtons['createBundle'] = '<button type="submit" name="createBundle" id="createBundle" class="tl_submit createBundle" accesskey="x">' . $GLOBALS['TL_LANG']['tl_contao_bundle_creator']['createBundleButton'] . '</button>';
        }

        return $arrButtons;
    }
}
