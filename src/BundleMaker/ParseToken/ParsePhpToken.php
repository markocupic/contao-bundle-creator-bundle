<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao Bundle Creator
 * @license    MIT
 * @see        https://github.com/markocupic/contao-bundle-creator-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken;

use Contao\File;
use Contao\Folder;
use Contao\System;

/**
 * Class ParsePhpToken
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken
 */
class ParsePhpToken
{

    /**
     * Save content to tmp file and parse content
     * (replace php tokens with tags from token storage)
     *
     *
     * @param string $content
     * @param array $arrTags
     * @return string
     * @throws \Exception
     */
    public static function parsePhpTokens(string $content, array $arrTags): string
    {
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        $folder = new Folder('system/tmp/bundle-creator-tmp');

        $tmp = new File('system/tmp/bundle-creator-tmp' . md5($content . microtime()));
        $tmp->append($content);
        $tmp->close();

        ob_start();
        extract($arrTags, EXTR_SKIP);
        include $projectDir . '/' . $tmp->path;
        $content = ob_get_clean();
        ob_end_flush();

        $tmp->delete();

        $folder->purge();
        $folder->delete();

        return $content;
    }

}
