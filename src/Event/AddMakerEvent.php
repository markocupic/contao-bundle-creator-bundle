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

use Contao\CoreBundle\Framework\ContaoFramework;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class AddMakerEvent extends Event
{
    public const NAME = 'markocupic.contao_bundle_creator_bundle.maker.added';

    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TagStorage
     */
    private $tagStorage;

    /**
     * @var FileStorage
     */
    private $fileStorage;

    /**
     * @var ContaoBundleCreatorModel
     */
    private $input;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var string
     */
    private $skeletonPath;

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(\stdClass $event)
    {
        $this->framework = $event->framework;
        $this->session = $event->session;
        $this->tagStorage = $event->tagStorage;
        $this->fileStorage = $event->fileStorage;
        $this->input = $event->input;
        $this->message = $event->message;
        $this->skeletonPath = $event->skeletonPath;
        $this->projectDir = $event->projectDir;
    }

    public function getFramework(): ContaoFramework
    {
        return $this->framework;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getTagStorage(): TagStorage
    {
        return $this->tagStorage;
    }

    public function getFileStorage(): FileStorage
    {
        return $this->fileStorage;
    }

    public function getInput(): ContaoBundleCreatorModel
    {
        return $this->input;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getSkeletonPath(): string
    {
        return $this->skeletonPath;
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }
}
