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

namespace Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Storage;

use Contao\File;
use Contao\StringUtil;
use Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Message\Message;
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
 * ->createFileFromString('destination/somefile.txt', 'Lorem ipsum');
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
 * @package Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator\Storage
 */
class FileStorage
{

    /** @var Message */
    private $message;

    /**
     * @var string
     */
    private $projectDir;

    /** @var array */
    private $arrStorrage = [];

    /** @var string */
    private $_current;

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

        $this->arrStorrage[$targetPath] = [
            'source'  => $sourcePath,
            'target'  => $targetPath,
            'content' => $objFile->getContent(),
        ];

        $this->_current = $targetPath;

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
            throw new \Exception(sprintf('File "%s" is already set. Please use FileStorage::getFile()->truncate()->appendContent($strContent) instead.', $targetPath));
        }

        $this->arrStorrage[$targetPath] = [
            'source'  => null,
            'target'  => $targetPath,
            'content' => $stringContent,
        ];

        $this->_current = $targetPath;

        return $this;
    }

    /**
     * @param string $targetPath
     * @return FileStorage
     * @throws \Exception
     */
    public function getFile(string $targetPath): self
    {
        if (!isset($this->arrStorrage[$targetPath]))
        {
            throw new \Exception(sprintf('File "%s" not found in the storage', $targetPath));
        }

        $this->_current = $targetPath;

        return $this;
    }

    /**
     * @param string $targetPath
     * @return bool
     */
    public function hasFile(string $targetPath): bool
    {
        if (!isset($this->arrStorrage[$targetPath]))
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
        if ($this->_current)
        {
            if (isset($this->arrStorrage[$this->_current]))
            {
                unset($this->arrStorrage[$this->_current]);
            }
        }

        $this->_current = null;

        return $this;
    }

    /**
     * @param string $strContent
     * @return FileStorage
     * @throws \Exception
     */
    public function appendContent(string $strContent): self
    {
        if (!isset($this->_current))
        {
            $this->sendFilePointerNotSetException();
        }

        $this->arrStorrage[$this->_current]['content'] .= $strContent;

        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getContent(): string
    {
        if (!isset($this->_current))
        {
            $this->sendFilePointerNotSetException();
        }

        return $this->arrStorrage[$this->_current]['content'];
    }

    /**
     * @return FileStorage
     * @throws \Exception
     */
    public function truncate(): self
    {
        if (!isset($this->_current))
        {
            $this->sendFilePointerNotSetException();
        }

        $this->arrStorrage[$this->_current]['content'] = '';

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
        if (!isset($this->_current))
        {
            $this->sendFilePointerNotSetException();
        }

        $content = $this->arrStorrage[$this->_current]['content'];
        $arrTags = $tagStorage->getAll();
        $this->arrStorrage[$this->_current]['content'] = StringUtil::parseSimpleTokens($content, $arrTags);

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function sendFilePointerNotSetException()
    {
        throw new \Exception('There is no pointer to a file. Please use FileStorage::getFile($sourceFile) or FileStorage::createFile($sourceFile, $targetFile) or FileStorage::createFileFromString($targetFile, $strContent)');
    }

}
