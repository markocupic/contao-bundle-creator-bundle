<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\ParseToken;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken\ParsePhpToken;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;

class ParsePhpTokenTest extends ContaoTestCase
{
    protected TagStorage $tagStorage;
    protected ParsePhpToken $parseToken;

    protected function setUp(): void
    {
        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());
        $this->tagStorage = new TagStorage();
        $this->tagStorage->set('Louis', 'XIV');
        $this->parseToken = new ParsePhpToken($this->tagStorage);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(ParsePhpToken::class, $this->parseToken);
    }

    /**
     * @throws \Exception
     */
    public function testParsePhpTokensFromString(): void
    {
        $strTest = 'Louis <?= $this->Louis ?>, known as Louis the Great or the Sun King (le Roi Soleil), was King of France from 14 May 1643 until his death in 1715.';
        $strReplaced = 'Louis XIV, known as Louis the Great or the Sun King (le Roi Soleil), was King of France from 14 May 1643 until his death in 1715.';
        $this->assertSame($strReplaced, $this->parseToken->parsePhpTokensFromString($strTest));

        $this->expectException(\Exception::class);
        $strTest = 'Louis <?= $this->chucknorris ?>, known as Louis the Great or the Sun King (le Roi Soleil), was King of France from 14 May 1643 until his death in 1715.';

        $this->parseToken->parsePhpTokensFromString($strTest);
    }
}
