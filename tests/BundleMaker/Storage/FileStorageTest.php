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

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Storage;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Markocupic\ContaoBundleCreatorBundle\Skeleton;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class FileStorageTest extends ContaoTestCase
{
    protected TagStorage $tagStorage;

    protected FileStorage $fileStorage;

    protected string $tmpSourceFile1 = '';

    protected string $tmpSourceFile2 = '';

    protected string $tmpSourceFile3 = '';

    protected string $tmpTargetDir = '';

    protected string $tmpTargetFile = '';

    protected function setUp(): void
    {
        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());

        // Set the default and custom template paths
        Skeleton::setDefaultPath(sys_get_temp_dir().\DIRECTORY_SEPARATOR.'skeleton');
        Skeleton::setCustomTemplatePath(sys_get_temp_dir().\DIRECTORY_SEPARATOR.'custom_skeleton');
        $fs = new Filesystem();
        $fs->mkdir(Skeleton::getDefaultPath());
        $fs->mkdir(Skeleton::getCustomTemplatePath());

        // Create the temp file
        $this->tmpSourceFile1 = Skeleton::getDefaultPath().\DIRECTORY_SEPARATOR.'test_source1.txt.ttpl';
        $fs->dumpFile($this->tmpSourceFile1, 'Here comes the content.');

        // Create the non-template-temp file
        $this->tmpSourceFile2 = Skeleton::getCustomTemplatePath().\DIRECTORY_SEPARATOR.'test_source4.txt';
        $fs->dumpFile($this->tmpSourceFile1, 'Here comes the content.');

        // Create the temp file
        $this->tmpSourceFile3 = Skeleton::getCustomTemplatePath().\DIRECTORY_SEPARATOR.'test_source3.txt.ttpl';
        $fs->dumpFile($this->tmpSourceFile1, 'Here comes the content.');

        // Create the target directory
        $this->tmpTargetDir = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'target_dir';
        $fs->mkdir($this->tmpTargetDir);

        // Set the target file path
        $this->tmpTargetFile = $this->tmpTargetDir.\DIRECTORY_SEPARATOR.'target1.txt';

        // Set the tag storage
        $this->tagStorage = new TagStorage();

        // Set file storage
        $loader = new FilesystemLoader(sys_get_temp_dir());
        $twig = new Environment($loader);
        $this->fileStorage = new FileStorage($twig);
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove(Skeleton::getDefaultPath());
        $fs->remove(Skeleton::getCustomTemplatePath());
        $fs->remove($this->tmpTargetDir);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage);
    }

    /**
     * @throws \Exception
     */
    public function testAddFile(): void
    {
        $this->fileStorage->addFile($this->tmpSourceFile1, $this->tmpTargetFile);
        $this->assertTrue(true === $this->fileStorage->has($this->tmpTargetFile));
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->getFile($this->tmpTargetFile));
        $this->assertSame('Here comes the content.', $this->fileStorage->getContent());

        // Do not allow overwriting files
        $this->expectException(\Exception::class);
        $this->fileStorage->addFile($this->tmpSourceFile1, $this->tmpTargetFile);
    }

    /**
     * @throws \Exception
     */
    public function testAddFileFromString(): void
    {
        // Another file
        $content = 'Foo';
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->addFileFromString($this->tmpTargetFile, $content));
        $this->assertSame('Foo', $this->fileStorage->getContent());

        // Do not allow overwriting files
        $this->expectException(\Exception::class);
        $this->fileStorage->addFileFromString($this->tmpTargetFile, $content);
    }

    /**
     * @throws \Exception
     */
    public function testReplaceContent(): void
    {
        $this->assertSame(
            'Bar',
            $this->fileStorage->addFile($this->tmpSourceFile1, $this->tmpTargetFile)
                ->replaceContent('Bar')
                ->getContent(),
        );
    }

    /**
     * @throws \Exception
     */
    public function testAppendContent(): void
    {
        $this->fileStorage->addFileFromString($this->tmpTargetFile, 'Foo');
        $this->assertSame('FooBar', $this->fileStorage->appendContent('Bar')->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testTruncate(): void
    {
        // Another file
        $this->assertSame(
            '',
            $this->fileStorage->addFile($this->tmpSourceFile1, $this->tmpTargetFile)
                ->truncate()
                ->getContent(),
        );
    }

    /**
     * @throws \Exception
     */
    public function testGetAll(): void
    {
        // Another file
        $target1 = $this->tmpTargetDir.\DIRECTORY_SEPARATOR.'target1.txt';
        $target2 = $this->tmpTargetDir.\DIRECTORY_SEPARATOR.'target2.txt';
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->addFileFromString($target1, 'Foo'));
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->addFileFromString($target2, 'Bar'));
        $this->assertTrue(2 === \count($this->fileStorage->getAll()));
    }

    /**
     * @throws \Exception
     */
    public function testSendFilePointerNotSetException(): void
    {
        $this->expectException(\Exception::class);
        $this->fileStorage->removeAll()->appendContent('Foo');
    }

    /**
     * Test isTemplate method returns true for files with .ttpl extension.
     *
     * @throws \Exception
     */
    public function testIsTemplateReturnsTrueForTemplateFile(): void
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->tmpSourceFile1, 'Plain content');

        $this->assertTrue(
            (new \ReflectionMethod(FileStorage::class, 'isTemplate'))
                ->invokeArgs($this->fileStorage, [new \SplFileObject($this->tmpSourceFile1)]),
        );
    }

    /**
     * Test isTemplate method returns false for non-template files.
     *
     * @throws \Exception
     */
    public function testIsTemplateReturnsFalseForNonTemplateFile(): void
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->tmpSourceFile2, 'Plain content');

        $this->assertFalse(
            (new \ReflectionMethod(FileStorage::class, 'isTemplate'))
                ->invokeArgs($this->fileStorage, [new \SplFileObject($this->tmpSourceFile2)]),
        );
    }

    /**
     * Test that getTagReplacedContentFromFilePath correctly replaces tags in a template file.
     *
     * @throws \Exception
     */
    public function testGetTagReplacedContentFromFilePathReplacesTags(): void
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->tmpSourceFile1, 'Hello, {{ name }}!');
        $this->tagStorage->set('name', 'John Doe');
        $content = $this->fileStorage->getTagReplacedContentFromFilePath($this->tmpSourceFile1, $this->tagStorage);
        $this->assertSame('Hello, John Doe!', $content);
    }

    /**
     * Test that getTagReplacedContentFromFilePath returns raw content for a non-template file.
     *
     * @throws \Exception
     */
    public function testGetTagReplacedContentFromFilePathReturnsRawContent(): void
    {
        // Test that getTagReplacedContentFromFilePath returns raw content if no tags are found.
        $fs = new Filesystem();
        $fs->dumpFile($this->tmpSourceFile1, 'Plain text content.');
        $content = $this->fileStorage->getTagReplacedContentFromFilePath($this->tmpSourceFile1, $this->tagStorage);
        $this->assertSame('Plain text content.', $content);

        // Test that getTagReplacedContentFromFilePath returns raw content for a non-template file.
        $fs->dumpFile($this->tmpSourceFile2, 'Hello, {{ name }}!');
        $this->tagStorage->set('name', 'John Doe');
        $content = $this->fileStorage->getTagReplacedContentFromFilePath($this->tmpSourceFile2, $this->tagStorage);
        $this->assertSame('Hello, {{ name }}!', $content);
    }
}
