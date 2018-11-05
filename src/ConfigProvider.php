<?php

declare(strict_types=1);

namespace Tobias\Zend\Expressive\Zf1Session;

use Zend\Expressive\Session\SessionPersistenceInterface;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases' => [
                SessionPersistenceInterface::class => Persistence\SessionPersistence::class,
            ],
            'factories' => [
                Persistence\SessionPersistence::class => Persistence\SessionPersistenceFactory::class,
            ],
        ];
    }
}
