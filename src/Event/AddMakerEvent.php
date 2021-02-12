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

namespace Markocupic\ContaoBundleCreatorBundle\Event;

use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Symfony\Contracts\EventDispatcher\Event;

class AddMakerEvent extends Event
{
    public const NAME = 'maker.added';

    private $fileStorage;

    private $tagStorage;

    private $arrInput;

    public function __construct(\stdClass $event)
    {
        $this->fileStorage = $event->fileStorage;
        $this->tagStorage = $event->tagStorage;
        $this->arrInput = $event->arrInput;
    }

    public function getFileStorage(): FileStorage
    {
        return $this->fileStorage;
    }

    public function getTagStorage(): TagStorage
    {
        return $this->tagStorage;
    }

    public function getArrInput(): array
    {
        return $this->arrInput;
    }
}
