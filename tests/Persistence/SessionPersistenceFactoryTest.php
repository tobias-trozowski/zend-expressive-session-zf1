<?php

declare(strict_types=1);

namespace TobiasTest\Expressive\Zf1Session\Persistence;

use Tobias\Zend\Expressive\Zf1Session\Persistence\SessionPersistence;
use Tobias\Zend\Expressive\Zf1Session\Persistence\SessionPersistenceFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Session\SessionPersistenceInterface;

class SessionPersistenceFactoryTest extends TestCase
{
    /**
     * @var SessionPersistenceFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new SessionPersistenceFactory();
    }

    public function testServiceWithConfigIsCreated(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn()->shouldBeCalledOnce();

        $instance = $this->factory->__invoke($container->reveal());

        $this->assertInstanceOf(SessionPersistence::class, $instance);
        $this->assertInstanceOf(SessionPersistenceInterface::class, $instance);
    }
}