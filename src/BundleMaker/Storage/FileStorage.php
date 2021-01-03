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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken\ParsePhpToken;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;

/**
 * Usage:.
 *
 * $fileStorage = new FileStorage();
 *
 * $fileStorage
 * ->addFile('somefolder/somefile.txt', 'destination/somefile.txt')
 * ->appendContent('bla, bla');
 *
 * or:
 * // Override file
 * $fileStorage
 * ->addFile('somefolder/somefile.txt', 'destination/somefile.txt', true);
 *
 * or:
 * // Create new file from string
 * $fileStorage
 * ->addFileFromString('destination/somefile.txt', 'Lorem ipsum',);
 *
 * or:
 *
 * if($fileStorage->hasFile('somefolder/someotherfile.txt'))
 * {
 *   $fileStorage
 *   ->getFile('somefolder/someotherfile.txt')
 *   ->truncate()
 *   ->appendContent('bla, bla');
 * }
 *
 *
 * Class FileStorage
 */
class FileStorage
{
    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var array
     */
    protected $arrStorrage = [];

    /**
     * @var int
     */
    protected $intIndex = -1;

    /**
     * FileStorage constructor.
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @throws \Exception
     *
     * @return FileStorage
     */
    public function addFile(string $sourcePath, string $targetPath, bool $blnForceOverride = false): self
    {
        if (!is_file($sourcePath)) {
            throw new FileNotFoundException(sprintf('File "%s" not found.', $sourcePath));
        }

        if ($this->hasFile($targetPath) && !$blnForceOverride) {
            throw new \Exception(sprintf('File "%s" is already set. Please use the $blnForceOverride parameter or call FileStorage::getFile()->replaceContent() instead.', $targetPath));
        }

        // Replace default source with a custom source
        // stored in the "templates/contao-bundle-creator-bundle/skeleton" directory
        $search = 'vendor/markocupic/contao-bundle-creator-bundle/src/Resources';
        $replace = 'templates/contao-bundle-creator-bundle';
        $customSource = str_replace($search, $replace, $sourcePath);

        if (is_file($customSource)) {
            $sourcePath = $customSource;
        }

        $arrData = [
            'source' => $sourcePath,
            'target' => $targetPath,
            'content' => file_get_contents($sourcePath),
        ];

        if (($index = $this->getIndexOf($targetPath)) < 0) {
            $this->arrStorrage[] = $arrData;
        } else {
            $this->arrStorrage[$index] = $arrData;
        }

        $this->intIndex = $this->getIndexOf($targetPath);

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function addFilesFromFolder(string $sourcePath, string $targetPath, bool $traverseSubdirectories = false, bool $blnForceOverride = false): void
    {
        if (!is_dir($sourcePath)) {
            throw new FileNotFoundException(sprintf('Folder "%s" not found.', $sourcePath));
        }

        $finder = new Finder();

        if (false === $traverseSubdirectories) {
            $finder->depth('== 0');
        }

        foreach ($finder->files()->in($sourcePath) as $file) {
            $basename = str_replace([$sourcePath, 'tpl.'], ['', ''], $file->getRealPath());
            $this->addFile($file->getRealPath(), $targetPath.$basename, $blnForceOverride);
        }
    }

    /**
     * @throws \Exception
     *
     * @return FileStorage
     */
    public function addFileFromString(string $targetPath, string $stringContent = '', bool $blnForceOverride = false): self
    {
        if ($this->hasFile($targetPath) && !$blnForceOverride) {
            throw new \Exception(sprintf('File "%s" is already set. Please use FileStorage::getFile()->replaceContent() instead.', $targetPath));
        }

        $arrData = [
            'source' => null,
            'target' => $targetPath,
            'content' => $stringContent,
        ];

        if (($index = $this->getIndexOf($targetPath)) < 0) {
            $this->arrStorrage[] = $arrData;
        } else {
            $this->arrStorrage[$index] = $arrData;
        }

        $this->intIndex = $this->getIndexOf($targetPath);

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return FileStorage
     */
    public function getFile(string $targetPath): self
    {
        if (($index = $this->getIndexOf($targetPath)) < 0) {
            throw new \Exception(sprintf('File "%s" not found in the storage', $targetPath));
        }

        $this->intIndex = $index;

        return $this;
    }

    public function hasFile(string $targetPath): bool
    {
        if ($this->getIndexOf($targetPath) < 0) {
            return false;
        }

        return true;
    }

    /**
     * @return FileStorage
     */
    public function removeFile(): self
    {
        if ($this->intIndex > -1) {
            if (isset($this->arrStorrage[$this->intIndex])) {
                unset($this->arrStorrage[$this->intIndex]);
            }
        }

        $this->intIndex = -1;

        return $this;
    }

    /**
     * @return FileStorage
     */
    public function removeAll(): self
    {
        $this->arrStorrage = [];
        $this->intIndex = -1;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return FileStorage
     */
    public function appendContent(string $strContent): self
    {
        if ($this->intIndex < 0) {
            throw $this->sendFilePointerNotSetException();
        }

        $this->arrStorrage[$this->intIndex]['content'] .= $strContent;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return FileStorage
     */
    public function replaceContent(string $strContent): self
    {
        if ($this->intIndex < 0) {
            throw $this->sendFilePointerNotSetException();
        }

        $this->arrStorrage[$this->intIndex]['content'] = $strContent;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function getContent(): string
    {
        if ($this->intIndex < 0) {
            throw $this->sendFilePointerNotSetException();
        }

        return (string) $this->arrStorrage[$this->intIndex]['content'];
    }

    /**
     * @throws \Exception
     *
     * @return FileStorage
     */
    public function truncate(): self
    {
        if ($this->intIndex < 0) {
            throw $this->sendFilePointerNotSetException();
        }

        $this->arrStorrage[$this->intIndex]['content'] = '';

        return $this;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->arrStorrage;
    }

    /**
     * Replace tags.
     *
     * @throws \Exception
     *
     * @return FileStorage
     */
    public function replaceTags(TagStorage $tagStorage): self
    {
        if ($this->intIndex < 0) {
            throw $this->sendFilePointerNotSetException();
        }

        $content = $this->arrStorrage[$this->intIndex]['content'];
        $templateParser = new ParsePhpToken($tagStorage);
        $this->arrStorrage[$this->intIndex]['content'] = $templateParser->parsePhpTokensFromString($content);

        return $this;
    }

    private function getIndexOf(string $targetPath): int
    {
        foreach ($this->arrStorrage as $index => $arrFile) {
            if ($arrFile['target'] === $targetPath) {
                return $index;
            }
        }

        return -1;
    }

    /**
     * @throws \Exception
     */
    private function sendFilePointerNotSetException()
    {
        return new \Exception('There is no pointer pointing to a file. Please use FileStorage::getFile() or FileStorage::addFile() or FileStorage::addFileFromString()');
    }
}
