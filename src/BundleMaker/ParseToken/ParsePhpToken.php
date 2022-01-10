<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ParsePhpToken.
 */
class ParsePhpToken
{
    /**
     * @var TagStorage
     */
    public $tagStorage;

    /**
     * ParsePhpToken constructor.
     */
    public function __construct(TagStorage $tagStorage)
    {
        $this->tagStorage = $tagStorage;
    }

    /**
     * Magic method: call tag storage properties in templates via $this->tokenname
     * Throw exception, if there is a call for a undefined property.
     *
     * @throws \Exception
     */
    public function __get(string $name): string
    {
        if (!$this->tagStorage->has($name)) {
            if (ob_get_status()['level'] > 0) {
                // Clean output buffer, otherwise unit test will fail
                // Test code or tested code did not (only) close its own output buffers
                // https://stackoverflow.com/questions/29122683/how-to-use-output-buffering-inside-phpunit-test
                ob_get_clean();
            }

            throw new \Exception(sprintf('Tag "%s" not found.', $name));
        }

        return $this->tagStorage->get($name);
    }

    /**
     * Save content to tmp file and parse content
     * (replace php tokens with tags from token storage).
     *
     * @throws \Exception
     */
    public function parsePhpTokensFromString(string $content): string
    {
        $objFile = new Filesystem();

        $tmpDir = sys_get_temp_dir();

        if (!is_dir($tmpDir)) {
            throw new \Exception(sprintf('Temporary directory not found.'));
        }

        $tmpFile = $tmpDir.'/'.md5($content.microtime()).'txt';
        $objFile->dumpFile($tmpFile, $content);

        if (!is_file($tmpFile)) {
            throw new \Exception(sprintf('Can not read from temp file "%s".', $tmpFile));
        }

        // Parse template
        ob_start();

        include $tmpFile;

        $content = ob_get_clean();

        $objFile->remove($tmpFile);

        return $content;
    }
}
