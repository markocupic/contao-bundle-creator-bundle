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

namespace Markocupic\ContaoBundleCreatorBundle\Event;

use Contao\CoreBundle\Framework\ContaoFramework;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{
    private ContaoFramework $framework;
    private RequestStack $requestStack;
    private TagStorage $tagStorage;
    private FileStorage $fileStorage;
    private ContaoBundleCreatorModel $input;
    private Message $message;
    private string $skeletonPath;
    private string $projectDir;
    private SessionInterface $session;

    public function __construct(\stdClass $event)
    {
        $this->framework = $event->framework;
        $this->requestStack = $event->requestStack;
        $this->tagStorage = $event->tagStorage;
        $this->fileStorage = $event->fileStorage;
        $this->input = $event->input;
        $this->message = $event->message;
        $this->skeletonPath = $event->skeletonPath;
        $this->projectDir = $event->projectDir;
        $this->session = $this->requestStack->getCurrentRequest()->getSession();
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
