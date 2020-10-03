<?php

declare(strict_types=1);

/*
 * This file is part of Contao Bundle Creator Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-bundle-creator-bundle
 */

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class BundleMaker.
 */
class Message
{
    /**
     * @var string
     */
    private const STR_INFO_FLASH_TYPE = 'contao.BE.info';

    /**
     * @var string
     */
    private const STR_ERROR_FLASH_TYPE = 'contao.BE.error';

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * Message constructor.
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Add an info message to the contao backend.
     */
    public function addInfo(string $msg): void
    {
        $this->addFlashMessage($msg, self::STR_INFO_FLASH_TYPE);
    }

    /**
     * Add an error message to the contao backend.
     */
    public function addError(string $msg): void
    {
        $this->addFlashMessage($msg, self::STR_ERROR_FLASH_TYPE);
    }

    /**
     * Get info messages.
     */
    public function getInfo(): array
    {
        return $this->getFlashMessages(self::STR_INFO_FLASH_TYPE);
    }

    /**
     * Get error messages.
     */
    public function getError(): array
    {
        return $this->getFlashMessages(self::STR_ERROR_FLASH_TYPE);
    }

    /**
     * Add a message to the contao backend.
     */
    private function addFlashMessage(string $msg, string $type): void
    {
        /** @var Session $session */
        $session = $this->session;
        $flashBag = $session->getFlashBag();
        $arrFlash = [];

        if ($flashBag->has($type)) {
            $arrFlash = $flashBag->get($type);
        }

        $arrFlash[] = $msg;

        $flashBag->set($type, $arrFlash);
    }

    /**
     * Get flash messages for the contao backend.
     */
    private function getFlashMessages(string $type): array
    {
        /** @var Session $session */
        $session = $this->session;
        $flashBag = $session->getFlashBag();
        $arrFlash = [];

        if ($flashBag->has($type)) {
            $arrFlash = $flashBag->get($type);
        }

        return $arrFlash;
    }
}
