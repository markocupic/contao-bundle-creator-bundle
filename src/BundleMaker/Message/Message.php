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

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Message as ContaoMessage;
use Symfony\Component\HttpFoundation\RequestStack;

class Message
{
    public const CONTAO_SCOPE = 'BE';
    public const SESSION_KEY_ERROR = 'contao.BE.error';
    public const SESSION_KEY_INFO = 'contao.BE.info';
    public const SESSION_KEY_CONFIRM = 'contao.BE.confirm';

    protected Adapter $messageAdapter;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly RequestStack $requestStack,
    ) {
        $this->messageAdapter = $this->framework->getAdapter(ContaoMessage::class);
    }

    public function hasInfo(): bool
    {
        return $this->messageAdapter->hasInfo(static::CONTAO_SCOPE);
    }

    public function hasError(): bool
    {
        return $this->messageAdapter->hasError(static::CONTAO_SCOPE);
    }

    public function hasConfirmation(): bool
    {
        return $this->messageAdapter->hasConfirmation(static::CONTAO_SCOPE);
    }

    /**
     * Add an info message to the contao backend.
     */
    public function addInfo(string $msg): void
    {
        $this->messageAdapter->addInfo($msg, static::CONTAO_SCOPE);
    }

    /**
     * Add an error message to the contao backend.
     */
    public function addError(string $msg): void
    {
        $this->messageAdapter->addError($msg, static::CONTAO_SCOPE);
    }

    /**
     * Add a confirmation message to the contao backend.
     */
    public function addConfirmation(string $msg): void
    {
        $this->messageAdapter->addConfirmation($msg, static::CONTAO_SCOPE);
    }

    /**
     * Get info messages.
     */
    public function getInfo(): array
    {
        return $this->getFlashMessages(self::SESSION_KEY_INFO);
    }

    /**
     * Get error messages.
     */
    public function getError(): array
    {
        return $this->getFlashMessages(self::SESSION_KEY_ERROR);
    }

    /**
     * Get confirmation messages.
     */
    public function getConfirmation(): array
    {
        return $this->getFlashMessages(self::SESSION_KEY_CONFIRM);
    }

    /**
     * Get flash messages for the contao backend.
     */
    private function getFlashMessages(string $type): array
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $flashBag = $session->getFlashBag();

        return $flashBag->get($type, []);
    }
}
