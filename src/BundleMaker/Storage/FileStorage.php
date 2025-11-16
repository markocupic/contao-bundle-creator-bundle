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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage;

use Markocupic\ContaoBundleCreatorBundle\Skeleton;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Twig\Environment;

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
 * // Create a new file from string
 * $fileStorage
 * ->addFileFromString('destination/somefile.txt', 'Lorem ipsum',);
 *
 * or:
 *
 * if($fileStorage->has('somefolder/someotherfile.txt'))
 * {
 *   $fileStorage
 *   ->getFile('somefolder/someotherfile.txt')
 *   ->truncate()
 *   ->appendContent('bla, bla');
 * }
 */
class FileStorage
{
    protected array $storage = [];

    protected int $intIndex = -1;

    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function addFile(string $sourcePath, string $targetPath, bool $forceOverride = false): self
    {
        $sourcePath = Path::canonicalize($sourcePath);
        $targetPath = Path::canonicalize($targetPath);

        if (!is_file($sourcePath)) {
            throw new FileNotFoundException(\sprintf('File "%s" not found.', $sourcePath));
        }

        if ($this->has($targetPath) && !$forceOverride) {
            throw new \Exception(\sprintf('File "%s" is already set. Please use the $forceOverride parameter or call FileStorage::getFile()->replaceContent() instead.', $targetPath));
        }

        // Replace the default source with a custom source
        // stored in the "templates/contao-bundle-creator-bundle/skeleton" directory
        $search = Skeleton::getDefaultPath();
        $replace = Skeleton::getCustomTemplatePath();

        $customSourcePath = str_replace($search, $replace, $sourcePath);

        if (is_file($customSourcePath)) {
            $sourcePath = $customSourcePath;
        }

        $data = [
            'source' => $sourcePath,
            'target' => $targetPath,
            'content' => file_get_contents($sourcePath),
        ];

        if (($index = $this->getIndexOf($targetPath)) < 0) {
            $this->storage[] = $data;
        } else {
            $this->storage[$index] = $data;
        }

        $this->intIndex = $this->getIndexOf($targetPath);

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function addFilesFromFolder(string $sourcePath, string $targetPath, bool $traverseSubdirectories = false, bool $forceOverride = false): array
    {
        $sourcePath = Path::canonicalize($sourcePath);
        $targetPath = Path::canonicalize($targetPath);

        if (!is_dir($sourcePath)) {
            throw new FileNotFoundException(\sprintf('Folder "%s" not found.', $sourcePath));
        }

        $finder = new Finder();

        if (false === $traverseSubdirectories) {
            $finder->depth('== 0');
        }

        $arrFiles = [];

        foreach ($finder->files()->ignoreDotFiles(false)->in($sourcePath) as $file) {
            $relPath = Path::makeRelative($file->getRealPath(), $sourcePath);

            if ('ttpl' === $file->getExtension()) {
                // Remove the .ttpl extension from the file path
                $relPath = preg_replace('/\.ttpl$/', '', $relPath);
            }

            $this->addFile($file->getRealPath(), Path::join($targetPath, $relPath), $forceOverride);
            $arrFiles[] = Path::join($targetPath, $relPath);
        }

        return $arrFiles;
    }

    /**
     * @throws \Exception
     */
    public function addFileFromString(string $targetPath, string $content = '', bool $forceOverride = false): self
    {
        $targetPath = Path::canonicalize($targetPath);

        if ($this->has($targetPath) && !$forceOverride) {
            throw new \Exception(\sprintf('File "%s" is already set. Please use FileStorage::getFile()->replaceContent() instead.', $targetPath));
        }

        $data = [
            'source' => null,
            'target' => $targetPath,
            'content' => $content,
        ];

        if (($index = $this->getIndexOf($targetPath)) < 0) {
            $this->storage[] = $data;
        } else {
            $this->storage[$index] = $data;
        }

        $this->intIndex = $this->getIndexOf($targetPath);

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function getFile(string $targetPath): self
    {
        $targetPath = Path::canonicalize($targetPath);

        if (($index = $this->getIndexOf($targetPath)) < 0) {
            throw new \Exception(\sprintf('File "%s" not found in the storage', $targetPath));
        }

        $this->intIndex = $index;

        return $this;
    }

    public function has(string $targetPath): bool
    {
        $targetPath = Path::canonicalize($targetPath);

        if ($this->getIndexOf($targetPath) < 0) {
            return false;
        }

        return true;
    }

    public function removeFile(): self
    {
        if ($this->intIndex > -1) {
            if (isset($this->storage[$this->intIndex])) {
                unset($this->storage[$this->intIndex]);
            }
        }

        $this->intIndex = -1;

        return $this;
    }

    public function removeAll(): self
    {
        $this->storage = [];
        $this->intIndex = -1;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function appendContent(string $strContent): self
    {
        if ($this->intIndex < 0) {
            throw $this->sendFilePointerNotSetException();
        }

        $this->storage[$this->intIndex]['content'] .= $strContent;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function replaceContent(string $strContent): self
    {
        if ($this->intIndex < 0) {
            throw $this->sendFilePointerNotSetException();
        }

        $this->storage[$this->intIndex]['content'] = $strContent;

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

        return (string) $this->storage[$this->intIndex]['content'];
    }

    /**
     * @throws \Exception
     */
    public function truncate(): self
    {
        if ($this->intIndex < 0) {
            throw $this->sendFilePointerNotSetException();
        }

        $this->storage[$this->intIndex]['content'] = '';

        return $this;
    }

    public function getAll(): array
    {
        return $this->storage;
    }

    /**
     * Replace tags.
     *
     * @throws \Exception
     */
    public function replaceTags(TagStorage $tagStorage, array $extensions = []): self
    {
        if ($this->intIndex < 0) {
            throw $this->sendFilePointerNotSetException();
        }

        $blnReplace = true;

        if (\count($extensions) > 0) {
            $blnReplace = false;

            foreach ($extensions as $extension) {
                if (isset($this->storage[$this->intIndex]['source'])) {
                    if (empty($this->storage[$this->intIndex]['source'])) {
                        continue;
                    }
                    $file = new \SplFileObject($this->storage[$this->intIndex]['source'], 'rb'); // 'rb' = read binary safe
                    if ($file->getExtension() === $extension) {
                        $blnReplace = true;
                    }
                }
            }
        }

        if ($blnReplace) {
            if ($this->isTemplate($file)) {
                $content = file_get_contents($file->getRealPath());
                $this->storage[$this->intIndex]['content'] = $this->twig->createTemplate($content)->render($tagStorage->getAll());
            }
        }

        return $this;
    }

    /**
     * Replace php tags and return content from partials.
     *
     * @throws \Exception
     */
    public function getTagReplacedContentFromFilePath(string $strPath, TagStorage $tagStorage): string
    {
        $strPath = Path::canonicalize($strPath);

        $file = new \SplFileObject($strPath);

        $content = $this->fileGetContents($file);

        if ($this->isTemplate($file)) {
            return $this->twig->createTemplate($content)->render($tagStorage->getAll());
        }

        return $content;
    }

    /**
     * Create the file in the target directory in vendor/vendorname/bundlename.
     */
    public function createFile(string $targetPath): void
    {
        $targetPath = Path::canonicalize($targetPath);

        if (!$this->has($targetPath)) {
            throw new \Exception(\sprintf('File "%s" not found in the storage', $targetPath));
        }

        $arrFile = $this->storage[$this->getIndexOf($targetPath)];

        $parentDir = \dirname($arrFile['target']);

        $fs = new Filesystem();

        if (!is_dir($parentDir)) {
            // Create directory recursive
            $fs->mkdir($parentDir);
        }

        $fs->dumpFile($arrFile['target'], $arrFile['content']);
    }

    private function getIndexOf(string $targetPath): int
    {
        foreach ($this->storage as $index => $arrFile) {
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

    private function isTemplate(\SplFileObject $file): bool
    {
        if (!is_file($file->getRealPath())) {
            throw new FileNotFoundException(\sprintf('File "%s" not found.', $file->getRealPath()));
        }

        if ('ttpl' === $file->getExtension()) {
            return true;
        }

        return false;
    }

    private function fileGetContents(\SplFileObject $file): string
    {
        $content = '';

        while (!$file->eof()) {
            $chunk = $file->fread(8192); // Read in 8KB chunks

            if (false === $chunk) {
                throw new \RuntimeException('Failed to read from file.');
            }

            $content .= $chunk;
        }

        return $content;
    }
}
