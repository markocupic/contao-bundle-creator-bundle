<?= "<?php\n" ?>

declare(strict_types=1);

<?= $this->phpdoc ?>

namespace <?= $this->toplevelnamespace ?>\<?= $this->sublevelnamespace ?>\Session\Attribute;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

/**
 * Provides an array access adapter for a session attribute bag.
 */
class ArrayAttributeBag extends AttributeBag implements \ArrayAccess
{
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function &offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }
}
