<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\EventSubscriber;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class BackendAssetsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Packages $packages,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'registerBackendAssets'];
    }

    public function registerBackendAssets(RequestEvent $event): void
    {
        if (!$this->scopeMatcher->isBackendRequest($event->getRequest())) {
            return;
        }

        // Back end menu icon
        $GLOBALS['TL_CSS'][] = $this->packages->getUrl('css/backend.css', 'markocupic_contao_bundle_creator');
    }
}
