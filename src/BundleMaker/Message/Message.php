<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Contao Bundle Creator
 * @license    MIT
 * @see        https://github.com/markocupic/contao-bundle-creator-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoBundleCreatorBundle\BundleMaker\Message;


use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class BundleMaker
 * @package Markocupic\ContaoBundleCreatorBundle\BundleMaker
 */
class Message
{
    /** @var SessionInterface */
    protected $session;

    /** @var string */
    private const STR_INFO_FLASH_TYPE = 'contao.BE.info';

    /** @var string */
    private const STR_ERROR_FLASH_TYPE = 'contao.BE.error';

    /**
     * Message constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Add an info message to the contao backend
     *
     * @param string $msg
     */
    public function addInfo(string $msg): void
    {
        $this->addFlashMessage($msg, self::STR_INFO_FLASH_TYPE);
    }

    /**
     * Add an error message to the contao backend
     *
     * @param string $msg
     */
    public function addError(string $msg): void
    {
        $this->addFlashMessage($msg, self::STR_ERROR_FLASH_TYPE);
    }

    /**
     * Add a message to the contao backend
     *
     * @param string $msg
     * @param string $type
     */
    private function addFlashMessage(string $msg, string $type): void
    {
        /** @var Session $session */
        $session = $this->session;
        $flashBag = $session->getFlashBag();
        $arrFlash = [];
        if ($flashBag->has($type))
        {
            $arrFlash = $flashBag->get($type);
        }

        $arrFlash[] = $msg;

        $flashBag->set($type, $arrFlash);
    }

}
