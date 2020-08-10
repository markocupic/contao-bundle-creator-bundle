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

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Message;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Class MessageTest
 *
 * @package Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Message
 */
class MessageTest extends ContaoTestCase
{

    /** @var string */
    protected $tmpTargetFile;

    public function setUp(): void
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
