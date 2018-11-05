<?php

declare(strict_types=1);

namespace Tobias\Zend\Expressive\Zf1Session;

use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;

use function array_key_exists;

/**
 * Class which provides zf1 session behaviour (session gets modified directly)
 */
class Session implements SessionInterface, SessionIdentifierAwareInterface
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $isRegenerated = false;

    /**
     * Original data provided to the constructor.
     *
     * @var array
     */
    private $originalData;

    /**
     * @var array
     */
    private $data;

    public function __construct(array &$session, string $id = '')
    {
        $this->originalData = $session;
        $this->data = &$session;
        $this->id = $id;
    }

    /**
     * Retrieve all data for purposes of persistence.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @param mixed  $default Default value to return if $name does not exist.
     *
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->data[$name] ?? $default;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function hasChanged(): bool
    {
        if ($this->isRegenerated) {
            return true;
        }

        return $this->data !== $this->originalData;
    }

    public function regenerate(): SessionInterface
    {
        $session = clone $this;
        $session->isRegenerated = true;

        return $session;
    }

    public function isRegenerated(): bool
    {
        return $this->isRegenerated;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
