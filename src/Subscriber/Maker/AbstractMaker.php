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

namespace Markocupic\ContaoBundleCreatorBundle\Subscriber\Maker;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\MakerInterface;

abstract class AbstractMaker implements MakerInterface
{
    /**
     * @var TagStorage
     */
    protected $tagStorage;

    /**
     * @var FileStorage
     */
    protected $fileStorage;

    /**
     * @var array
     */
    protected $arrInput;

    /**
     * @var false|string
     */
    protected $projectDir;

    /**
     * @var false|string
     */
    protected $skeletonPath;

    public function addFilesToStorage(AddMakerEvent $event): void
    {
        $this->tagStorage = $event->getTagStorage();
        $this->fileStorage = $event->getFileStorage();
        $this->arrInput = $event->getArrInput();
        $this->projectDir = realpath(__DIR__.'/../../../../../../');
        $this->skeletonPath = realpath(__DIR__.'/../../Resources/skeleton');
    }
}
