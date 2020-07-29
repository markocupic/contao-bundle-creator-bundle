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
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;

/**
 * Class ParsePhpToken
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken
 */
class ParsePhpToken
{
    /** @var TagStorage */
    public $tagStorage;

    /**
     * ParsePhpToken constructor.
     * @param TagStorage $tagStorage
     */
    public function __construct(TagStorage $tagStorage)
    {
        $this->tagStorage = $tagStorage;
    }

    /**
     * Magic method: call tag storage properties in templates via $this->tokenname
     * Throw exception, if there is a call for a undefined property
     *
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public function __get(string $name): string
    {
        if (!$this->tagStorage->has($name))
        {
            throw new \Exception(sprintf('Tag "%s" not found.', $name));
        }
        else
        {
            return $this->tagStorage->get($name);
        }
    }

    /**
     * Save content to tmp file and parse content
     * (replace php tokens with tags from token storage)
     *
     * @param string $content
     * @return string
     * @throws \Exception
     */
    public function parsePhpTokens(string $content): string
    {
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        $folder = new Folder('system/tmp/bundle-creator-tmp');

        $tmp = new File('system/tmp/bundle-creator-tmp' . md5($content . microtime()));
        $tmp->append($content);
        $tmp->close();

        if (!is_file($projectDir . '/' . $tmp->path))
        {
            throw \Exception(sprintf('Can not read from temp file "%s".', $tmp->path));
        }

        // Parse template
        ob_start();

        include $projectDir . '/' . $tmp->path;
        $content = ob_get_clean();

        ob_end_flush();

        $tmp->delete();

        $folder->purge();
        $folder->delete();

        return $content;
    }

}
