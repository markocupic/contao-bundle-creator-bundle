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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken\ParsePhpToken;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Usage:
 *
 * $fileStorage = new FileStorage();
 *
 * $fileStorage
 * ->addFile('somefolder/somefile.txt', 'destination/somefile.txt')
 * ->appendContent('bla, bla');
 *
 * or:
 *
 * $fileStorage = new FileStorage();
 *
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
 *
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage
 */
class FileStorage
{

    /** @var array */
    private $arrStorrage = [];

    /** @var int */
    private $intIndex = -1;

    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @return FileStorage
     * @throws \Exception
     */
    public function addFile(string $sourcePath, string $targetPath): self
    {

        if (!is_file($sourcePath))
        {
            throw new FileNotFoundException(sprintf('File "%s" not found.', $sourcePath));
        }

        if ($this->hasFile($targetPath))
        {
            throw new \Exception(sprintf('File "%s" is already set. Please use FileStorage::getFile()->replaceContent() instead.', $targetPath));
        }

        $arrData = [
            'source'  => $sourcePath,
            'target'  => $targetPath,
            'content' => file_get_contents($sourcePath),
        ];

        if (($index = $this->getIndexOf($targetPath)) < 0)
        {
            $this->arrStorrage[] = $arrData;
        }
        else
        {
            $this->arrStorrage[$index] = $arrData;
        }

        $this->intIndex = $this->getIndexOf($targetPath);

        return $this;
    }

    /**
     * @param string $targetPath
     * @return int
     */
    private function getIndexOf(string $targetPath): int
    {

        foreach ($this->arrStorrage as $index => $arrFile)
        {
            if ($arrFile['target'] === $targetPath)
            {
                return $index;
            }
        }

        return -1;
    }

    /**
     * @param string $targetPath
     * @param string $stringContent
     * @return FileStorage
     * @throws \Exception
     */
    public function addFileFromString(string $targetPath, string $stringContent = ''): self
    {

        if ($this->hasFile($targetPath))
        {
            throw new \Exception(sprintf('File "%s" is already set. Please use FileStorage::getFile()->replaceContent() instead.', $targetPath));
        }

        $arrData = [
            'source'  => null,
            'target'  => $targetPath,
            'content' => $stringContent,
        ];

        if (($index = $this->getIndexOf($targetPath)) < 0)
        {
            $this->arrStorrage[] = $arrData;
        }
        else
        {
            $this->arrStorrage[$index] = $arrData;
        }

        $this->intIndex = $this->getIndexOf($targetPath);

        return $this;
    }

    /**
     * @param string $targetPath
     * @return FileStorage
     * @throws \Exception
     */
    public function getFile(string $targetPath): self
    {

        if (($index = $this->getIndexOf($targetPath)) < 0)
        {
            throw new \Exception(sprintf('File "%s" not found in the storage', $targetPath));
        }

        $this->intIndex = $index;

        return $this;
    }

    /**
     * @param string $targetPath
     * @return bool
     */
    public function hasFile(string $targetPath): bool
    {

        if ($this->getIndexOf($targetPath) < 0)
        {
            return false;
        }

        return true;
    }

    /**
     * @return FileStorage
     */
    public function removeFile(): self
    {

        if ($this->intIndex > -1)
        {
            if (isset($this->arrStorrage[$this->intIndex]))
            {
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
     * @param string $strContent
     * @return FileStorage
     * @throws \Exception
     */
    public function appendContent(string $strContent): self
    {

        if ($this->intIndex < 0)
        {
            throw $this->sendFilePointerNotSetException();
        }

        $this->arrStorrage[$this->intIndex]['content'] .= $strContent;

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function sendFilePointerNotSetException()
    {

        return new \Exception('There is no pointer pointing to a file. Please use FileStorage::getFile() or FileStorage::addFile() or FileStorage::addFileFromString()');
    }

    /**
     * @param string $strContent
     * @return FileStorage
     * @throws \Exception
     */
    public function replaceContent(string $strContent): self
    {

        if ($this->intIndex < 0)
        {
            throw $this->sendFilePointerNotSetException();
        }

        $this->arrStorrage[$this->intIndex]['content'] = $strContent;

        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getContent(): string
    {

        if ($this->intIndex < 0)
        {
            throw $this->sendFilePointerNotSetException();
        }

        return (string) $this->arrStorrage[$this->intIndex]['content'];
    }

    /**
     * @return FileStorage
     * @throws \Exception
     */
    public function truncate(): self
    {

        if ($this->intIndex < 0)
        {
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
     * Replace tags
     *
     * @param TagStorage $tagStorage
     * @return FileStorage
     * @throws \Exception
     */
    public function replaceTags(TagStorage $tagStorage): self
    {

        if ($this->intIndex < 0)
        {
            throw $this->sendFilePointerNotSetException();
        }

        $content = $this->arrStorrage[$this->intIndex]['content'];
        $templateParser = new ParsePhpToken($tagStorage);
        $this->arrStorrage[$this->intIndex]['content'] = $templateParser->parsePhpTokensFromString($content);

        return $this;
    }

}
