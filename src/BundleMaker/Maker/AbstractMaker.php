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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Maker;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\MakerInterface;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;

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

    /**
     * AbstractMaker constructor.
     */
    public function __construct(TagStorage $tagStorage, FileStorage $fileStorage, array $arrInput)
    {
        $this->tagStorage = $tagStorage;
        $this->fileStorage = $fileStorage;
        $this->arrInput = $arrInput;
        $this->projectDir = realpath(__DIR__.'/../../../../../../');
        $this->skeletonPath = realpath(__DIR__.'/../../Resources/skeleton');
    }
}
