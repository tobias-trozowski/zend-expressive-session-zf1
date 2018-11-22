<?php

declare(strict_types=1);

namespace Tobias\Zend\Expressive\Zf1Session\Persistence;

use Psr\Container\ContainerInterface;
use function array_merge;
use function ini_get;

final class SessionPersistenceFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return SessionPersistence
     */
    public function __invoke(ContainerInterface $container): SessionPersistence
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $options = array_merge($this->defaultOptions(), $config['session'] ?? []);

        return new SessionPersistence($options);
    }

    /**
     * Returns a list of default options
     *
     * @return iterable List with default options and values, can be nested
     */
    public function defaultOptions(): iterable
    {
        // setting default values which would be overwritten by Zend_Session
        return [
            'use_cookies' => false,
            'use_only_cookies' => true,
            'cache_limiter' => ini_get('session.cache_limiter'),
            'cache_expire' => (int)ini_get('session.cache_expire'),
            'cookie_path' => ini_get('session.cookie_path'),
            'cookie_lifetime' => (int)ini_get('session.cookie_lifetime'),
        ];
    }
}
