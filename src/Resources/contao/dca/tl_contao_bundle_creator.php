<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao Bundle Creator
 * @licence    MIT
 * @see        https://github.com/markocupic/contao-bundle-creator
 *
 */

/**
 * Table tl_contao_bundle_creator
 */
$GLOBALS['TL_DCA']['tl_contao_bundle_creator'] = [

    // Config
    'config'      => [
        'dataContainer'     => 'Table',
        'enableVersioning'  => true,
        'sql'               => [
            'keys' => [
                'id' => 'primary'
            ]
        ],
        'onsubmit_callback' => [
            ['tl_contao_bundle_creator', 'runCreator']
        ]
    ],
    'edit'        => [
        'buttons_callback' => [
            ['tl_contao_bundle_creator', 'buttonsCallback']
        ]
    ],
    'list'        => [
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['bundlename'],
            'flag'        => 1,
            'panelLayout' => 'filter;sort,search,limit'
        ],
        'label'             => [
            'fields' => ['bundlename'],
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
    'palettes'    => [
        '__selector__' => ['addDcaTable'],
        'default'      => '{bundle_settings_legend},bundlename,vendorname,repositoryname,overwriteexisting;{composer_settings_legend},composerdescription,licence,authorname,authoremail,authorwebsite;{dcatable_settings_legend},addDcaTable'
    ],
    // Subpalettes
    'subpalettes' => [
        'addDcaTable' => 'dcatable',
    ],
    // Fields
    'fields'      => [
        'id'                  => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'              => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'bundlename'          => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'vendorname'          => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'repositoryname'      => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'overwriteexisting'   => [
            'inputType' => 'checkbox',
            'exclude'   => true,
            'sorting'   => true,
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'addDcaTable'         => [
            'inputType' => 'checkbox',
            'exclude'   => true,
            'sorting'   => true,
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'composerdescription' => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'licence'             => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'authorname'          => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alpha'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'authoremail'         => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'email'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'authorwebsite'       => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'url'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'addDcaTable'         => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'dcatable'            => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
    ]
];

/**
 * Class tl_contao_bundle_creator
 */
class tl_contao_bundle_creator extends Contao\Backend
{

    /**
     * tl_contao_bundle_creator constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * onsubmit_callback
     * @param \Contao\DataContainer $dc
     */
    public function runCreator(Contao\DataContainer $dc)
    {
        if (Contao\Input::get('id') != '' && Contao\Input::post('createBundle') === '' && Contao\Input::post('FORM_SUBMIT') === 'tl_contao_bundle_creator' && Contao\Input::post('SUBMIT_TYPE') != 'auto')
        {
            if (null !== ($objModel = Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel::findByPk(Contao\Input::get('id'))))
            {
                /** @var  Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\ExtensionGenerator $objInit */
                $objInit = Contao\System::getContainer()->get(Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\ExtensionGenerator::class);
                $objInit->run($objModel);
            }
        }
    }

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
