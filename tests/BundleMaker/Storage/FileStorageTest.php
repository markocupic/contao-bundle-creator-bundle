<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Mein Steckbrief
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-steckbrief-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Storage;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;

/**
 * Class FileStorageTest
 *
 * @package Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Storage
 */
class FileStorageTest extends ContaoTestCase
{
    /** @var TagStorage */
    protected $tagStorage;

    /** @var FileStorage */
    protected $fileStorage;

    /** @var string */
    protected $tmpSourceFile;

    /** @var string */
    protected $tmpTargetFile;

    public function setUp(): void
    {
        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());
        $this->tagStorage = new TagStorage();
        $this->fileStorage = new FileStorage();

        // Create temp file
        $this->tmpSourceFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'source.txt';
        $fh = fopen($this->tmpSourceFile, 'w');
        fwrite($fh, 'Here comes the content.');
        fclose($fh);

        // Set target file path
        $this->tmpTargetFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'target.txt';
    }

    public function tearDown(): void
    {
        if (file_exists($this->tmpSourceFile) === true)
        {
            unlink($this->tmpSourceFile);
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
        $this->fileStorage->addFile($this->tmpSourceFile, $this->tmpTargetFile);
        $this->assertTrue(true === $this->fileStorage->hasFile($this->tmpTargetFile));
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->getFile($this->tmpTargetFile));
        $this->assertSame('Here comes the content.', $this->fileStorage->getContent());

        // Do not allow overwriting files
        $this->expectException(\Exception::class);
        $this->fileStorage->addFile($this->tmpSourceFile, $this->tmpTargetFile);
    }

    /**
     * @throws \Exception
     */
    public function testAddFileFromString(): void
    {
        // Another file
        $target = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'target.txt';
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
            $this->fileStorage->addFile($this->tmpSourceFile, $this->tmpTargetFile)
                ->replaceContent('Bar')
                ->getContent()
        );
    }

    /**
     * @throws \Exception
     */
    public function testAppendContent(): void
    {
        $target1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'target1.txt';
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
            $this->fileStorage->addFile($this->tmpSourceFile, $this->tmpTargetFile)
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
        $target1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'target1.txt';
        $target2 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'target2.txt';
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->addFileFromString($target1, 'Foo'));
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->addFileFromString($target2, 'Bar'));
        $this->assertTrue(2 === count($this->fileStorage->getAll()));
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
    public function testReplaceTags()
    {
        $strContent = '<?= $this->actor ?> was an American actor.';
        $strExpected = 'Charles Bronson was an American actor.';
        $this->tagStorage->set('actor', 'Charles Bronson');

        $target1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'target1.txt';
        $this->fileStorage->addFileFromString($target1, $strContent);

        $this->assertSame($strExpected, $this->fileStorage->replaceTags($this->tagStorage)->getContent());
    }

}
