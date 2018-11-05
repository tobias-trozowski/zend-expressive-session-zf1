<?php

declare(strict_types=1);

namespace Tobias\Zend\Expressive\Zf1Session\Persistence;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesDefaultOptions;
use Psr\Container\ContainerInterface;

use function ini_get;

final class SessionPersistenceFactory implements ProvidesDefaultOptions
{
    use ConfigurationTrait;

    /**
     * @param ContainerInterface $container
     *
     * @return SessionPersistence
     */
    public function __invoke(ContainerInterface $container): SessionPersistence
    {
        /** @var array $options */
        $options = $this->optionsWithFallback($container->get('config'));
        return new SessionPersistence($options);
    }

    /**
     * @inheritdoc \Interop\Config\RequiresConfig::dimensions
     */
    public function dimensions(): iterable
    {
        return ['session'];
    }

    /**
     * Returns a list of default options, which are merged in \Interop\Config\RequiresConfig::options()
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
