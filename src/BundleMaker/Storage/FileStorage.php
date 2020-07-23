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

use Contao\File;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\SimpleToken\SimpleTokenParser;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Usage:
 *
 * $fileStorage = new FileStorage();
 *
 * $fileStorage
 * ->createFile('somefolder/somefile.txt', 'destination/somefile.txt')
 * ->appendContent('bla,bla');
 *
 * or:
 *
 * $fileStorage = new FileStorage();
 *
 * $fileStorage
 * ->createFileFromString('destination/somefile.txt', 'Lorem ipsum',);
 *
 * or:
 *
 * if($fileStorage->hasFile('somefolder/someotherfile.txt'))
 * {
 *   $fileStorage
 *   ->getFile('somefolder/someotherfile.txt')
 *   ->truncate()
 *   ->appendContent('bla,bla');
 * }
 *
 *
 * Class FileStorage
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage
 */
class FileStorage
{

    /** @var Message */
    private $message;

    /** @var string */
    private $projectDir;

    /** @var array */
    private $arrStorrage = [];

    /** @var int */
    private $intIndex = -1;

    /**
     * FileStorage constructor.
     * @param Message $message
     * @param string $projectDir
     */
    public function __construct(Message $message, string $projectDir)
    {
        $this->message = $message;
        $this->projectDir = $projectDir;
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @return FileStorage
     * @throws \Exception
     */
    public function createFile(string $sourcePath, string $targetPath): self
    {
        if (!is_file($this->projectDir . '/' . $sourcePath))
        {
            throw new FileNotFoundException(sprintf('File "%s" not found.', $this->projectDir . '/' . $sourcePath));
        }

        $objFile = new File($sourcePath);

        $arrData = [
            'source'  => $sourcePath,
            'target'  => $targetPath,
            'content' => $objFile->getContent(),
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
     * @param string $stringContent
     * @return FileStorage
     * @throws \Exception
     */
    public function createFileFromString(string $targetPath, string $stringContent = ''): self
    {
        if (isset($this->arrStorrage[$targetPath]))
        {
            throw new \Exception(sprintf('File "%s" is already set. Please use FileStorage::getFile($targetPath)->replaceContent($strContent) instead.', $targetPath));
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
     * @param string $strContent
     * @return FileStorage
     * @throws \Exception
     */
    public function appendContent(string $strContent): self
    {
        if ($this->intIndex < 0)
        {
            $this->sendFilePointerNotSetException();
        }

        $this->arrStorrage[$this->intIndex]['content'] .= $strContent;

        return $this;
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
            $this->sendFilePointerNotSetException();
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
            $this->sendFilePointerNotSetException();
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
            $this->sendFilePointerNotSetException();
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
            $this->sendFilePointerNotSetException();
        }

        $content = $this->arrStorrage[$this->intIndex]['content'];
        $arrTags = $tagStorage->getAll();
        $this->arrStorrage[$this->intIndex]['content'] = SimpleTokenParser::parseSimpleTokens($content, $arrTags);

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
     * @throws \Exception
     */
    private function sendFilePointerNotSetException()
    {
        throw new \Exception('There is no pointer to a file. Please use FileStorage::getFile($sourceFile) or FileStorage::createFile($sourceFile, $targetFile) or FileStorage::createFileFromString($targetFile, $strContent)');
    }

}
