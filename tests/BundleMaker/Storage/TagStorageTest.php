<?php

declare(strict_types=1);

/*
 * This file is part of a markocupic Contao Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author Marko Cupic
 * @package Contao Bundle Creator Bundle
 * @license MIT
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @see https://github.com/markocupic/conao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Storage;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;

/**
 * Class TagStorageTest.
 */
class TagStorageTest extends ContaoTestCase
{
    /**
     * @var TagStorage
     */
    protected $tagStorage;

    protected function setUp(): void
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
        $this->assertTrue(1 === \count($this->tagStorage->getAll()));
    }

    public function testRemoveAll(): void
    {
        $this->tagStorage->set('foo', 'bar');
        $this->tagStorage->set('Louis', 'XIV');
        $this->assertTrue(2 === \count($this->tagStorage->getAll()));
        $this->tagStorage->removeAll();
        $this->assertTrue(0 === \count($this->tagStorage->getAll()));
    }
}
