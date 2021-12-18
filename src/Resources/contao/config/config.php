<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
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

/*
 * Licenses
 *
 * @see https://docs.github.com/en/github/creating-cloning-and-archiving-repositories/licensing-a-repository
 */
$GLOBALS['contao_bundle_creator']['licenses'] = [
    'AFL-3.0' => 'Academic Free License v3.0',
    'APACHE-2.0' => 'Apache license 2.0',
    'ARTISTIC-2.0' => 'Artistic license 2.0',
    'BSL-1.0' => 'Boost Software License 1.0',
    'BSD-2-CLAUSE' => 'BSD 2-clause "Simplified" license',
    'BSD-3-CLAUSE' => 'BSD 3-clause "New" or "Revised" license',
    'BSD-3-CLAUSE-CLEAR' => 'BSD 3-clause Clear license',
    'CC' => 'Creative Commons license family',
    'CC0-1.0' => 'Creative Commons Zero v1.0 Universal',
    'CC-BY-4.0' => 'Creative Commons Attribution 4.0',
    'CC-BY-SA-4.0' => 'Creative Commons Attribution Share Alike 4.0',
    'WTFPL' => 'Do What The F*ck You Want To Public License',
    'ECL-2.0' => 'Educational Community License v2.0',
    'EPL-1.0' => 'Eclipse Public License 1.0',
    'EUPL-1.1' => 'European Union Public License 1.1',
    'AGPL-3.0' => 'GNU Affero General Public License v3.0',
    'GPL' => 'GNU General Public License family',
    'GPL-2.0' => 'GNU General Public License v2.0',
    'GPL-3.0' => 'GNU General Public License v3.0',
    'GPL-3.0-or-later' => 'GNU General Public License v3.0 or later',
    'LGPL' => 'GNU Lesser General Public License family',
    'LGPL-2.1' => 'GNU Lesser General Public License v2.1',
    'LGPL-3.0' => 'GNU Lesser General Public License v3.0',
    'LGPL-3.0-or-later' => 'GNU Lesser General Public License v3.0 or later',
    'ISC' => 'ISC',
    'LPPL-1.3C' => 'LaTeX Project Public License v1.3c',
    'MS-PL' => 'Microsoft Public License',
    'MIT' => 'MIT',
    'MPL-2.0' => 'Mozilla Public License 2.0',
    'OSL-3.0' => 'Open Software License 3.0',
    'POSTGRESQL' => 'PostgreSQL License',
    'OFL-1.1' => 'SIL Open Font License 1.1',
    'NCSA' => 'University of Illinois/NCSA Open Source License',
    'UNLICENSE' => 'The Unlicense',
    'ZLIB' => 'zLib License',
];
