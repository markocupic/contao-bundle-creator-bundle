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

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Storage;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;

/**
 * Class FileStorageTest.
 */
class FileStorageTest extends ContaoTestCase
{
    /**
     * @var TagStorage
     */
    protected $tagStorage;

    /**
     * @var FileStorage
     */
    protected $fileStorage;

    /**
     * @var string
     */
    protected $tmpSourceFile1;

    /**
     * @var string
     */
    protected $tmpTargetFile;

    protected function setUp(): void
    {
        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());
        $this->tagStorage = new TagStorage();
        $this->fileStorage = new FileStorage(System::getContainer()->getParameter('kernel.project_dir'));

        // Create temp file
        $this->tmpSourceFile1 = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'source1.txt';
        file_put_contents($this->tmpSourceFile1, 'Here comes the content.');

        // Set target file path
        $this->tmpTargetFile = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'target1.txt';

        // Create temp file
        $this->tmpSourceFile2 = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'source2.txt';
        file_put_contents($this->tmpSourceFile2, '<?= $this->actor ?> is a famous actor.');
        $this->tagStorage->set('actor', 'Charles Bronson');
    }

    protected function tearDown(): void
    {
        if (true === file_exists($this->tmpSourceFile1)) {
            unlink($this->tmpSourceFile1);
        }

        if (true === file_exists($this->tmpSourceFile2)) {
            unlink($this->tmpSourceFile2);
        }
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
        $this->assertTrue(true === $this->fileStorage->hasFile($this->tmpTargetFile));
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
        $target = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'target.txt';
        $content = 'Foo';
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->addFileFromString($target, $content));
        $this->assertSame('Foo', $this->fileStorage->getContent());

        // Do not allow overwriting files
        $this->expectException(\Exception::class);
        $this->fileStorage->addFileFromString($target, $content);
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
                ->getContent()
        );
    }

    /**
     * @throws \Exception
     */
    public function testAppendContent(): void
    {
        $target1 = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'target1.txt';
        $this->fileStorage->addFileFromString($target1, 'Foo');
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
                ->getContent()
        );
    }

    /**
     * @throws \Exception
     */
    public function testGetAll(): void
    {
        // Another file
        $target1 = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'target1.txt';
        $target2 = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'target2.txt';
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
     * @throws \Exception
     */
    public function testReplaceTags(): void
    {
        $strContent = '<?= $this->actor ?> was an American actor.';
        $strExpected = 'Charles Bronson was an American actor.';
        $this->tagStorage->set('actor', 'Charles Bronson');

        $target1 = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'target1.txt';
        $this->fileStorage->addFileFromString($target1, $strContent);

        $this->assertSame($strExpected, $this->fileStorage->replaceTags($this->tagStorage, [])->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testGetTagReplacedContentFromFilePath(): void
    {
        $strExpected = 'Charles Bronson is a famous actor.';
        $this->assertSame($strExpected, $this->fileStorage->getTagReplacedContentFromFilePath($this->tmpSourceFile2, $this->tagStorage));
    }
}
