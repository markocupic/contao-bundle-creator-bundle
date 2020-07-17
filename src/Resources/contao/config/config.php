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
 * Backend modules
 */
$GLOBALS['BE_MOD']['dev_tools']['contao_bundle_creator'] = array(
    'tables' => ['tl_contao_bundle_creator']
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_contao_bundle_creator'] = \Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel::class;




