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

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\ParseToken;

use Contao\TestCase\ContaoTestCase;
use Contao\System;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\ParseToken\ParsePhpToken;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;

/**
 * Class ParsePhpTokenTest
 *
 * @package Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker\ParseToken
 */
class ParsePhpTokenTest extends ContaoTestCase
{
    /** @var TagStorage */
    protected $tagStorage;

    /** @var ParsePhpToken */
    protected $parseToken;

    public function setUp(): void
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
