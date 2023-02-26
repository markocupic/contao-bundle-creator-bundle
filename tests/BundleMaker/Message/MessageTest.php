<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\Message;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Message as ContaoMessage;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class MessageTest extends ContaoTestCase
{
    protected Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $session = new Session(new MockArraySessionStorage());
        $session->start();

        $this->session = $session;

        $request = new Request([], [], [], [], [], [], [], json_encode([
            'foo' => 'bar',
        ]));

        $request->setSession($session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        // Do your tests
        $this->message = new Message($this->mockFramework(), $requestStack);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(Message::class, $this->message);
    }

    public function testHasMessage(): void
    {
        $this->message->addInfo('something');
        $this->assertTrue($this->message->hasInfo());

        $this->message->addError('something');
        $this->assertTrue($this->message->hasError());

        $this->message->addConfirmation('something');
        $this->assertTrue($this->message->hasConfirmation());
    }

    public function testGetMessage(): void
    {
        $bag = $this->session->getFlashBag();
        $bag->set(Message::SESSION_KEY_INFO, ['something']);
        $bag->set(Message::SESSION_KEY_ERROR, ['something wrong']);
        $bag->set(Message::SESSION_KEY_CONFIRM, ['something else']);

        $this->assertSame('something', $this->message->getInfo()[0]);
        $this->assertSame('something wrong', $this->message->getError()[0]);
        $this->assertSame('something else', $this->message->getConfirmation()[0]);
    }

    private function mockFramework($expectError = true): ContaoFramework
    {
        $adapter = $this->mockAdapter(['addInfo', 'addError', 'addConfirmation', 'hasInfo', 'hasError', 'hasConfirmation']);

        $adapter
            ->method('addInfo')
            ->with('something')
        ;

        $adapter
            ->method('addError')
            ->with('something')
        ;

        $adapter
            ->method('addConfirmation')
            ->with('something')
        ;

        $adapter
            ->method('hasInfo')
            ->willReturn(true)
        ;

        $adapter
            ->method('hasError')
            ->willReturn(true)
        ;

        $adapter
            ->method('hasConfirmation')
            ->willReturn(true)
        ;

        $adapters = [
            ContaoMessage::class => $adapter,
        ];

        return $this->mockContaoFramework($adapters);
    }
}
