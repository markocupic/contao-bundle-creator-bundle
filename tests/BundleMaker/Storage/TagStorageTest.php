<?php

/**
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 *
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
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;

/**
 * Class TagStorageTest
 *
 * @package Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Storage
 */
class TagStorageTest extends ContaoTestCase
{
    /** @var TagStorage */
    protected $tagStorage;

    public function setUp(): void
    {

        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());
        $this->tagStorage = new TagStorage();
    }

    public function testInstantiation(): void
    {

        $this->assertInstanceOf(TagStorage::class, $this->tagStorage);
    }

    public function testGetAll(): void
    {

        $this->assertSame([], $this->tagStorage->getAll());
        $this->tagStorage->set('foo', 'bar');
        $this->tagStorage->set('Louis', 'XIV');
        $this->assertSame(['foo' => 'bar', 'Louis' => 'XIV'], $this->tagStorage->getAll());
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

}
