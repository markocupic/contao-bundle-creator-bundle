<?php

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

use Contao\Backend;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Contao\System;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\BundleMaker;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Table tl_contao_bundle_creator
 */
$GLOBALS['TL_DCA']['tl_contao_bundle_creator'] = array(
	// Config
	'config'      => array(
		'dataContainer'     => 'Table',
		'enableVersioning'  => true,
		'sql'               => array(
			'keys' => array(
				'id' => 'primary'
			)
		),
		'onload_callback'   => array(
			array('tl_contao_bundle_creator', 'downloadZipFile')
		),
		'onsubmit_callback' => array(
			array('tl_contao_bundle_creator', 'runCreator')
		)
	),
	'edit'        => array(
		'buttons_callback' => array(
			array('tl_contao_bundle_creator', 'buttonsCallback')
		)
	),
	'list'        => array(
		'sorting'           => array(
			'mode'        => 2,
			'fields'      => array('bundlename'),
			'flag'        => 1,
			'panelLayout' => 'filter;sort,search,limit'
		),
		'label'             => array(
			'fields' => array('bundlename'),
			'format' => '%s',
		),
		'global_operations' => array(
			'all' => array(
				'href'       => 'act=select',
				'class'      => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations'        => array(
			'edit'   => array(
				'href' => 'act=edit',
				'icon' => 'edit.gif'
			),
			'copy'   => array(
				'href' => 'act=copy',
				'icon' => 'copy.svg'
			),
			'delete' => array(
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show'   => array(
				'href'       => 'act=show',
				'icon'       => 'show.gif',
				'attributes' => 'style="margin-right:3px"'
			),
		)
	),
	// Palettes
	'palettes'    => array(
		'__selector__' => array('editRootComposer', 'addBackendModule', 'addFrontendModule', 'addContentElement'),
		'default'      => '{bundle_settings_legend},bundlename,vendorname,repositoryname,overwriteexisting;
        {composer_settings_legend},composerdescription,composerlicense,composerauthorname,composerauthoremail,composerauthorwebsite,composerpackageversion;
        {rootcomposer_settings_legend},editRootComposer;
        {dcatable_settings_legend},addBackendModule;
        {frontendmodule_settings_legend},addFrontendModule;
        {contentelement_settings_legend},addContentElement;
        {custom_route_settings_legend},addCustomRoute;
        {coding_style_legend},addEasyCodingStandard'
	),
	// Subpalettes
	'subpalettes' => array(
		'editRootComposer'  => 'rootcomposerextendrepositorieskey',
		'addBackendModule'  => 'dcatable,backendmodulecategory,backendmodulecategorytrans,backendmoduletype,backendmoduletrans',
		'addFrontendModule' => 'frontendmodulecategory,frontendmodulecategorytrans,frontendmoduletype,frontendmoduletrans',
		'addContentElement' => 'contentelementcategory,contentelementcategorytrans,contentelementtype,contentelementtrans',
	),
	// Fields
	'fields'      => array(
		'id'                                => array(
			'sql' => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp'                            => array(
			'sql' => "int(10) unsigned NOT NULL default '0'"
		),
		'bundlename'                        => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr', 'rgxp' => 'alnum'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'vendorname'                        => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_vendorname'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'repositoryname'                    => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'doNotCopy' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_repositoryname', 'placeholder' => 'e.g. contao-pet-collection-bundle'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'overwriteexisting'                 => array(
			'inputType' => 'checkbox',
			'exclude'   => true,
			'sorting'   => true,
			'eval'      => array('tl_class' => 'clr'),
			'sql'       => "char(1) NOT NULL default ''"
		),
		'composerdescription'               => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'composerpackageversion'            => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => false, 'maxlength' => 16, 'tl_class' => 'w50', 'rgxp' => 'alnum'),
			'sql'       => "varchar(16) NOT NULL default ''"
		),
		'composerlicense'                   => array(
			'inputType'        => 'select',
			'exclude'          => true,
			'sorting'          => true,
			'options_callback' => array('tl_contao_bundle_creator', 'getLicenses'),
			'default'          => 'GPL-3.0-or-later',
			'flag'             => 1,
			'search'           => true,
			'eval'             => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'),
			'sql'              => "varchar(255) NOT NULL default ''"
		),
		'composerauthorname'                => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alpha'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'composerauthoremail'               => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'email'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'composerauthorwebsite'             => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'url'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'editRootComposer'                  => array(
			'inputType' => 'checkbox',
			'exclude'   => true,
			'sorting'   => true,
			'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr'),
			'sql'       => "char(1) NOT NULL default ''"
		),
		'rootcomposerextendrepositorieskey' => array(
			'inputType' => 'select',
			'exclude'   => true,
			'options'   => array('path', 'vcs-github'),
			'sorting'   => true,
			'eval'      => array('includeBlankOption' => false, 'tl_class' => 'clr'),
			'sql'       => "varchar(16) NOT NULL default ''"
		),
		'addBackendModule'                  => array(
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50 clr'),
			'sql'       => "char(1) NOT NULL default ''"
		),
		'dcatable'                          => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'clr', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_dcatable', 'placeholder' => 'e.g. tl_pets'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'backendmodulecategory'             => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_backendmodulecategory', 'placeholder' => 'e.g. pet_modules'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'backendmodulecategorytrans'        => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'backendmoduletype'                 => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_backendmoduletype', 'placeholder' => 'e.g. pet_collection'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'backendmoduletrans'                => array(
			'inputType' => 'text',
			'exclude'   => true,
			'search'    => true,
			'filter'    => true,
			'sorting'   => true,
			'eval'      => array('mandatory' => true, 'multiple' => true, 'size' => 2, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'addFrontendModule'                 => array(
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50 clr'),
			'sql'       => "char(1) NOT NULL default ''"
		),
		'frontendmodulecategory'            => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_frontendmodulecategory', 'placeholder' => 'e.g. pet_modules'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'frontendmodulecategorytrans'       => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'frontendmoduletype'                => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_frontendmoduletype', 'placeholder' => 'e.g. pet_listing_module'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'frontendmoduletrans'               => array(
			'inputType' => 'text',
			'exclude'   => true,
			'search'    => true,
			'filter'    => true,
			'sorting'   => true,
			'eval'      => array('mandatory' => true, 'multiple' => true, 'size' => 2, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'addContentElement'                 => array(
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50 clr'),
			'sql'       => "char(1) NOT NULL default ''"
		),
		'contentelementcategory'            => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_contentelementcategory', 'placeholder' => 'e.g. image_elements'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'contentelementcategorytrans'       => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'rgxp' => 'alnum'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'contentelementtype'                => array(
			'inputType' => 'text',
			'exclude'   => true,
			'sorting'   => true,
			'flag'      => 1,
			'search'    => true,
			'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'nospace' => true, 'decodeEntities' => true, 'rgxp' => 'cbcb_contentelementtype', 'placeholder' => 'e.g. heroimage_element'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'contentelementtrans'               => array(
			'inputType' => 'text',
			'exclude'   => true,
			'search'    => true,
			'filter'    => true,
			'sorting'   => true,
			'eval'      => array('mandatory' => true, 'multiple' => true, 'size' => 2, 'decodeEntities' => true, 'rgxp' => 'alnum', 'tl_class' => 'w50'),
			'sql'       => "varchar(255) NOT NULL default ''"
		),
		'addCustomRoute'                    => array(
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => false, 'tl_class' => 'w50 clr'),
			'sql'       => "char(1) NOT NULL default ''"
		),
		'addEasyCodingStandard'                    => array(
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => false, 'tl_class' => 'w50 clr'),
			'sql'       => "char(1) NOT NULL default ''"
		),
	)
);

/**
 * Class tl_contao_bundle_creator
 */
class tl_contao_bundle_creator extends Backend
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
	 * @param  DataContainer $dc
	 * @throws Exception
	 */
	public function runCreator(DataContainer $dc)
	{
		if (Input::get('id') != '' && Input::post('createBundle') === '' && Input::post('FORM_SUBMIT') === 'tl_contao_bundle_creator' && Input::post('SUBMIT_TYPE') != 'auto')
		{
			if (null !== ($objModel = ContaoBundleCreatorModel::findByPk(Input::get('id'))))
			{
				/** @var BundleMaker $bundleMakerService */
				$bundleMakerService = System::getContainer()->get('markocupic.contao_bundle_creator_bundle.bundle_maker.bundle_maker');
				$bundleMakerService->run($objModel);
			}
		}
	}

	/**
	 * onload callback
	 * Download extension as zip file when clicking on the download button
	 *
	 * @param DC_Table $dc
	 */
	public function downloadZipFile(DC_Table $dc)
	{
		if (Input::get('id') != '' && Input::post('downloadBundle') === '' && Input::post('FORM_SUBMIT') === 'tl_contao_bundle_creator' && Input::post('SUBMIT_TYPE') != 'auto')
		{
			/** @var SessionInterface $session */
			$session = System::getContainer()->get('session');

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
	 * @param  DC_Table $dc
	 * @return mixed
	 */
	public function buttonsCallback($arrButtons, DC_Table $dc)
	{
		if (Input::get('act') === 'edit')
		{
			$arrButtons['createBundle'] = '<button type="submit" name="createBundle" id="createBundle" class="tl_submit createBundle" accesskey="x">' . $GLOBALS['TL_LANG']['tl_contao_bundle_creator']['createBundleButton'] . '</button>';

			/** @var SessionInterface $session */
			$session = System::getContainer()->get('session');

			if ($session->has('CONTAO-BUNDLE-CREATOR.LAST-ZIP'))
			{
				$arrButtons['downloadBundle'] = '<button type="submit" name="downloadBundle" id="downloadBundle" class="tl_submit downloadBundle" accesskey="d" onclick="this.style.display = \'none\'">' . $GLOBALS['TL_LANG']['tl_contao_bundle_creator']['downloadBundleButton'] . '</button>';
			}
		}

		return $arrButtons;
	}

	/**
	 * @return array
	 */
	public function getLicenses(): array
	{
		$arrLicenses = array();

		foreach ($GLOBALS['contao_bundle_creator']['licenses'] as $k => $v)
		{
			$arrLicenses[$k] = "$k   ($v)";
		}

		return $arrLicenses;
	}
}
