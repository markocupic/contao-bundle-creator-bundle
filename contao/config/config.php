<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;

/*
 * Backend modules
 */
$GLOBALS['BE_MOD']['dev_tools']['contao_bundle_creator'] = [
    'tables' => ['tl_contao_bundle_creator'],
];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_contao_bundle_creator'] = ContaoBundleCreatorModel::class;
