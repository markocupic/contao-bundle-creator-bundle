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

namespace Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator;

use Contao\File;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Usage:
 *
 * $fileStorage = new FileStorage();
 *
 * $fileStorage
 * ->addFile('somefolder/somefile.txt', 'destination/somefile.txt')
 * ->addContent('bla,bla');
 *
 * if($fileStorage->hasFile('somefolder/someotherfile.txt'))
 * {
 *   $fileStorage
 *   ->getFile('somefolder/someotherfile.txt')
 *   ->truncate()
 *   ->addContent('bla,bla');
 * }
 *
 *
 * Class FileStorage
 * @package Markocupic\ContaoBundleCreatorBundle\ExtensionGenerator
 */
class FileStorage
{

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
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @param string $sourcePath
     * @param string $destinationPath
     * @return FileStorage
     * @throws \Exception
     */
    public function addFile(string $sourcePath, string $destinationPath): self
    {
        if (!is_file($this->projectDir . '/' . $sourcePath))
        {
            throw new FileNotFoundException(sprintf('File "%s" not found.', $this->projectDir . '/' . $sourcePath));
        }

        $objFile = new File($sourcePath);

        $this->arrStorrage[$sourcePath] = [
            'source'  => $sourcePath,
            'target'  => $destinationPath,
            'content' => $objFile->getContent(),
        ];

        $this->_current = $sourcePath;

        return $this;
    }

    /**
     * @param string $sourcePath
     * @return FileStorage
     * @throws \Exception
     */
    public function getFile(string $sourcePath): self
    {
        if (!isset($this->arrStorrage[$sourcePath]))
        {
            throw new \Exception(sprintf('File "%s" not found in the storage', $sourcePath));
        }

        $this->_current = $sourcePath;

        return $this;
    }

    /**
     * @param string $sourcePath
     * @return bool
     */
    public function hasFile(string $sourcePath): bool
    {
        if (!isset($this->arrStorrage[$sourcePath]))
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
    public function addContent(string $strContent): self
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
     * @throws \Exception
     */
    private function sendFilePointerNotSetException()
    {
        throw new \Exception('There is no pointer to a file. Please use FileStorage::getFile($sourceFile) or FileStorage::addFile($sourceFile, $targetFile)');
    }

}
