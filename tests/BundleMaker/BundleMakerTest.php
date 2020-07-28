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

namespace Markocupic\ContaoBundleMakerBundle\Tests\BundleMaker;


use Contao\TestCase\ContaoTestCase;
use Contao\System;


class BundleMakerTest extends ContaoTestCase
{


    public function setUp(): void
    {
        parent::setUp();
        System::setContainer($this->getContainerWithContaoConfiguration());

    }

    public function testCanBeInstantiated()
    {
        echo System::getContainer()->getParameter('kernel.project_dir');

        //$instance = $this->container->get(\Markocupic\ContaoBundleMakerBundle\BundleMaker\BundleMaker::class);
        //$this->assertInstanceOf(RszSteckbriefReaderModuleController::class, $instance);
    }
}
