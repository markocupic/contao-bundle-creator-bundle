<?= "<?php\n"; ?>

declare(strict_types=1);

<?= $this->phpdoc; ?>

namespace <?= $this->toplevelnamespace; ?>\<?= $this->sublevelnamespace; ?>\Session;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionFactory implements SessionFactoryInterface
{
    public function __construct(
        private readonly SessionFactoryInterface $inner,
        private readonly SessionBagInterface $sessionBag,
    ) {
    }

    public function createSession(): SessionInterface
    {
        $session = $this->inner->createSession();
        $session->registerBag($this->sessionBag);

        return $session;
    }
}
