<?php

declare(strict_types=1);

namespace TobiasTest\Expressive\Zf1Session\Persistence;

use Dflydev\FigCookies\FigResponseCookies;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tobias\Zend\Expressive\Zf1Session\Persistence\SessionPersistence;
use Tobias\Zend\Expressive\Zf1Session\Session;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;
use Zend_Session;
use function session_name;
use function time;

/**
 * @runTestsInSeparateProcesses
 */
class SessionPersistenceTest extends TestCase
{
    public function testSessionCreated(): void
    {
        $persistance = new SessionPersistence();
        $request = new ServerRequest();

        $session = $persistance->initializeSessionFromRequest($request);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertInstanceOf(SessionInterface::class, $session);
    }

    public function testSessionPersisted(): void
    {
        $persistance = new SessionPersistence();
        $response = new Response();
        $session = $this->getMockBuilder([SessionInterface::class, SessionIdentifierAwareInterface::class]);
        $session = $session->getMock();

        $session->method('toArray')->willReturn(['foo' => 'bar']);
        $session->method('getId')->willReturn('use-this-id');

        $response = $persistance->persistSession($session, $response);

        $this->assertSame(['foo' => 'bar'], $_SESSION);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testSessionRegenerate(): void
    {
        $persistance = new SessionPersistence();
        $response = new Response();
        $_SESSION = [];
        $session = new Session($_SESSION, 'use-this-id');
        $session->set('foo', 'bar');

        Zend_Session::start();
        $response = $persistance->persistSession($session->regenerate(), $response);

        $this->assertSame(['foo' => 'bar'], $_SESSION);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testNewSessionNeverWritten(): void
    {
        $persistance = new SessionPersistence();
        $responseObj = new Response();
        $_SESSION = [];
        $session = new Session($_SESSION);

        Zend_Session::start();
        $response = $persistance->persistSession($session, $responseObj);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($responseObj, $response);
    }

    public function testSessionCookieLifetime(): void
    {
        $persistance = new SessionPersistence(['cookie_lifetime' => 60]);
        $responseObj = new Response();
        $_SESSION = [];
        $session = new Session($_SESSION, 'use-this-id');
        Zend_Session::start();

        $timestamp = time() + 60;
        $response = $persistance->persistSession($session, $responseObj);
        $cookie = FigResponseCookies::get($response, session_name());

        $this->assertSame($timestamp, $cookie->getExpires());
    }
}
