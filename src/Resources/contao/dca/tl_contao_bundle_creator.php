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
        'onload_callback'   => [
            ['tl_contao_bundle_creator', 'downloadZipFile']
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
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'href' => 'act=edit',
                'icon' => 'edit.gif'
            ],
            'copy'   => [
                'href' => 'act=copy',
                'icon' => 'copy.svg'
            ],
            'delete' => [
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show'   => [
                'href'       => 'act=show',
                'icon'       => 'show.gif',
                'attributes' => 'style="margin-right:3px"'
            ],
        ]
    ],
    // Palettes
    'palettes'    => [
        '__selector__' => ['addBackendModule', 'addFrontendModule'],
        'default'      => '{bundle_settings_legend},bundlename,vendorname,repositoryname,overwriteexisting;{composer_settings_legend},composerdescription,composerlicense,composerauthorname,composerauthoremail,composerauthorwebsite,composerpackageversion;{rootcomposer_settings_legend},rootcomposerextendrepositorieskey,rootcomposerextendrequirekey;{dcatable_settings_legend},addBackendModule;{frontendmodule_settings_legend},addFrontendModule;{custom_route_settings_legend},addCustomRoute'
    ],
    // Subpalettes
    'subpalettes' => [
        'addBackendModule'  => 'dcatable,backendmodulecategory,backendmodulecategorytrans,backendmoduletype,backendmoduletrans',
        'addFrontendModule' => 'frontendmodulecategory,frontendmodulecategorytrans,frontendmoduletype,frontendmoduletrans',
    ],
    // Fields
    'fields'      => [
        'id'                                => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'                            => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'bundlename'                        => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'vendorname'                        => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'repositoryname'                    => [
            'inputType' => 'text',
            'default'   => 'contao-...',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'doNotCopy' => true, 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'overwriteexisting'                 => [
            'inputType' => 'checkbox',
            'exclude'   => true,
            'sorting'   => true,
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'composerdescription'               => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'composerpackageversion'            => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => false, 'maxlength' => 16, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(16) NOT NULL default ''"
        ],
        'composerlicense'                   => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'composerauthorname'                => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alpha'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'composerauthoremail'               => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'email'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'composerauthorwebsite'             => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'url'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'rootcomposerextendrequirekey'      => [
            'inputType' => 'select',
            'exclude'   => true,
            'options'   => ['path', 'vcs-github'],
            'sorting'   => true,
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'clr'],
            'sql'       => "varchar(16) NOT NULL default ''"
        ],
        'rootcomposerextendrepositorieskey' => [
            'inputType' => 'checkbox',
            'exclude'   => true,
            'sorting'   => true,
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'addBackendModule'                  => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'dcatable'                          => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr', 'nospace' => true, 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'backendmodulecategory'             => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'backendmoduletype'                 => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'backendmodulecategorytrans'        => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'backendmoduletrans'                => [
            'inputType' => 'text',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'eval'      => ['mandatory' => true, 'multiple' => true, 'size' => 2, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'addFrontendModule'                 => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'frontendmodulecategory'            => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'frontendmoduletype'                => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'frontendmodulecategorytrans'       => [
            'inputType' => 'text',
            'exclude'   => true,
            'sorting'   => true,
            'flag'      => 1,
            'search'    => true,
            'eval'      => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'frontendmoduletrans'               => [
            'inputType' => 'text',
            'exclude'   => true,
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'eval'      => ['mandatory' => true, 'multiple' => true, 'size' => 2, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'clr'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'addCustomRoute'                    => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => false, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''"
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
     * onsubmit callback
     * Run the bundle maker
     *
     * @param \Contao\DataContainer $dc
     * @throws Exception
     */
    public function runCreator(Contao\DataContainer $dc)
    {

        if (Contao\Input::get('id') != '' && Contao\Input::post('createBundle') === '' && Contao\Input::post('FORM_SUBMIT') === 'tl_contao_bundle_creator' && Contao\Input::post('SUBMIT_TYPE') != 'auto')
        {
            if (null !== ($objModel = Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel::findByPk(Contao\Input::get('id'))))
            {
                /** @var  Markocupic\ContaoBundleCreatorBundle\BundleMaker\BundleMaker $bundleMakerService */
                $bundleMakerService = Contao\System::getContainer()->get('markocupic.contaobundlecreatorbundle.bundlemaker.bundlemaker');
                $bundleMakerService->run($objModel);
            }
        }
    }

    /**
     * onload callback
     * Download extension as zip file when clicking on the download button
     *
     * @param Contao\DC_Table $dc
     */
    public function downloadZipFile(Contao\DC_Table $dc)
    {

        if (Contao\Input::get('id') != '' && Contao\Input::post('downloadBundle') === '' && Contao\Input::post('FORM_SUBMIT') === 'tl_contao_bundle_creator' && Contao\Input::post('SUBMIT_TYPE') != 'auto')
        {
            /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
            $session = Contao\System::getContainer()->get('session');
            if ($session->has('CONTAO-BUNDLE-CREATOR.LAST-ZIP'))
            {
                $zipSrc = $session->get('CONTAO-BUNDLE-CREATOR.LAST-ZIP');
                $session->remove('CONTAO-BUNDLE-CREATOR.LAST-ZIP');

                $projectDir = System::getContainer()->getParameter('kernel.project_dir');

                $filepath = $projectDir . '/' . $zipSrc;
                $filename = basename($filepath);
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($filepath));
                readfile($filepath);
                exit();
            }
        }
    }

    /**
     * @param $arrButtons
     * @param Contao\DC_Table $dc
     * @return mixed
     */
    public function buttonsCallback($arrButtons, Contao\DC_Table $dc)
    {

        if (Contao\Input::get('act') === 'edit')
        {
            $arrButtons['createBundle'] = '<button type="submit" name="createBundle" id="createBundle" class="tl_submit createBundle" accesskey="x">' . $GLOBALS['TL_LANG']['tl_contao_bundle_creator']['createBundleButton'] . '</button>';

            /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
            $session = Contao\System::getContainer()->get('session');
            if ($session->has('CONTAO-BUNDLE-CREATOR.LAST-ZIP'))
            {
                $arrButtons['downloadBundle'] = '<button type="submit" name="downloadBundle" id="downloadBundle" class="tl_submit downloadBundle" accesskey="d" onclick="this.style.display = \'none\'">' . $GLOBALS['TL_LANG']['tl_contao_bundle_creator']['downloadBundleButton'] . '</button>';
            }
        }

        return $arrButtons;
    }
}
