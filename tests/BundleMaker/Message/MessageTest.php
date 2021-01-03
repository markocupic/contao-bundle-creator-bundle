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

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Message;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Class MessageTest.
 */
class MessageTest extends ContaoTestCase
{
    /**
     * @var string
     */
    protected $tmpTargetFile;

    protected function setUp(): void
    {
        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());

        $session = new Session(new MockArraySessionStorage());
        $this->message = new Message($session);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(Message::class, $this->message);
    }

    public function testAdd(): void
    {
        $this->message->addInfo('Info text 1.');
        $this->message->addInfo('Info text 2.');

        $this->message->addError('Error text 1.');
        $this->message->addError('Error text 2.');

        $this->assertSame('Info text 1.', $this->message->getInfo()[0]);
        $this->assertSame('Error text 2.', $this->message->getError()[1]);
    }
}
