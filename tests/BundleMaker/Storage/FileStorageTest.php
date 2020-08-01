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
    public function testCreateFile(): void
    {
        $this->fileStorage->createFile($this->tmpSourceFile, $this->tmpTargetFile);
        $this->assertTrue(true === $this->fileStorage->hasFile($this->tmpTargetFile));
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->getFile($this->tmpTargetFile));
        $this->assertSame('Here comes the content.', $this->fileStorage->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testCreateFileFromString(): void
    {
        // Another file
        $target = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'target.txt';
        $content = 'Foo';
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->createFileFromString($target, $content));
        $this->assertSame('Foo', $this->fileStorage->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testReplaceContent(): void
    {
        $this->assertSame(
            'Bar',
            $this->fileStorage->createFile($this->tmpSourceFile, $this->tmpTargetFile)
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
        $this->fileStorage->createFileFromString($target1, 'Foo');
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
            $this->fileStorage->createFile($this->tmpSourceFile, $this->tmpTargetFile)
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
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->createFileFromString($target1, 'Foo'));
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage->createFileFromString($target2, 'Bar'));
        $this->assertTrue(2 === count($this->fileStorage->getAll()));
    }

    /**
     * @throws \Exception
     */
    public function testGet(): void
    {
        $this->tagStorage->set('foo', 'bar');
        $this->assertSame('bar', $this->tagStorage->get('foo'));
        $this->expectException(\Exception::class);
        $this->tagStorage->get('bar');
    }

    public function testHas(): void
    {
        $this->tagStorage->set('foo', 'bar');
        $this->assertTrue(true === $this->tagStorage->has('foo'));
        $this->assertTrue(false === $this->tagStorage->has('bar'));
    }

    public function testRemove(): void
    {
        $this->tagStorage->set('foo', 'bar');
        $this->tagStorage->set('Louis', 'XIV');
        $this->tagStorage->remove('Louis');
        $this->assertTrue(['foo' => 'bar'] === $this->tagStorage->getAll());
        $this->assertTrue(false === $this->tagStorage->has('Louis'));
        $this->assertTrue(true === $this->tagStorage->has('foo'));
        $this->assertTrue(1 === count($this->tagStorage->getAll()));
    }

    public function testRemoveAll(): void
    {
        $this->tagStorage->set('foo', 'bar');
        $this->tagStorage->set('Louis', 'XIV');
        $this->assertTrue(2 === count($this->tagStorage->getAll()));
        $this->tagStorage->removeAll();
        $this->assertTrue(0 === count($this->tagStorage->getAll()));
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
        $this->fileStorage->createFileFromString($target1, $strContent);

        $this->assertSame($strExpected, $this->fileStorage->replaceTags($this->tagStorage)->getContent());
    }

}
