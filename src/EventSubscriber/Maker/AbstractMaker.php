<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\EventSubscriber\Maker;

use Contao\CoreBundle\Framework\ContaoFramework;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message\Message;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\FileStorage;
use Markocupic\ContaoBundleCreatorBundle\BundleMaker\Storage\TagStorage;
use Markocupic\ContaoBundleCreatorBundle\Event\AddMakerEvent;
use Markocupic\ContaoBundleCreatorBundle\Event\AddTagsEvent;
use Markocupic\ContaoBundleCreatorBundle\EventSubscriber\MakerInterface;
use Markocupic\ContaoBundleCreatorBundle\Model\ContaoBundleCreatorModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class AbstractMaker implements MakerInterface, EventSubscriberInterface
{
    protected ContaoFramework $framework;
    protected SessionInterface $session;
    protected TagStorage $tagStorage;
    protected FileStorage $fileStorage;
    protected ContaoBundleCreatorModel $input;
    protected Message $message;
    protected string $skeletonPath;
    protected string $projectDir;

    public function addFilesToStorage(AddMakerEvent $event): void
    {
        $this->initialize($event);
    }

    public function addTagsToStorage(AddTagsEvent $event): void
    {
        $this->initialize($event);
    }

    /**
     * @param AddTagsEvent|AddMakerEvent $event
     */
    private function initialize($event): void
    {
        $this->framework = $event->getFramework();
        $this->session = $event->getSession();
        $this->tagStorage = $event->getTagStorage();
        $this->fileStorage = $event->getFileStorage();
        $this->input = $event->getInput();
        $this->message = $event->getMessage();
        $this->skeletonPath = $event->getSkeletonPath();
        $this->projectDir = $event->getProjectDir();
    }
}
