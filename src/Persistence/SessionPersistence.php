<?php

declare(strict_types=1);

namespace Tobias\Zend\Expressive\Zf1Session\Persistence;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobias\Zend\Expressive\Zf1Session\Session;
use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionPersistenceInterface;
use Zend_Session;

use function bin2hex;
use function random_bytes;
use function session_name;
use function session_start;
use function session_write_close;
use function time;

final class SessionPersistence implements SessionPersistenceInterface
{
    /** @var string */
    private $scriptFile;

    /**
     * @var array
     */
    private $sessionParams;

    /**
     * @var string
     */
    private $cookiePath;

    /**
     * @var int
     */
    private $cookieLifetime;

    public function __construct(array $sessionParams = [])
    {
        $this->sessionParams = $sessionParams;
        $this->cookiePath = $sessionParams['cookie_path'] ?? '/';
        $this->cookieLifetime = (int)($sessionParams['cookie_lifetime'] ?? 0);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return SessionInterface
     * @throws \Zend_Session_Exception
     */
    public function initializeSessionFromRequest(ServerRequestInterface $request): SessionInterface
    {
        $this->scriptFile = $request->getServerParams()['SCRIPT_FILENAME'] ?? __FILE__;
        $sessionId = FigRequestCookies::get($request, session_name())->getValue() ?? '';
        $id = $sessionId ?: $this->generateSessionId();
        $this->startSession($id, $this->sessionParams);

        return new Session($_SESSION, $id);
    }

    /**
     * Generate a session identifier.
     */
    private function generateSessionId(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * @param string $id
     * @param array  $options Additional options to pass to `session_start()`.
     *
     * @throws \Zend_Session_Exception
     */
    private function startSession(string $id, array $options = []): void
    {
        Zend_Session::setId($id);
        Zend_Session::start($options);
    }

    /**
     * @param SessionInterface  $session
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws \Zend_Session_Exception
     */
    public function persistSession(SessionInterface $session, ResponseInterface $response): ResponseInterface
    {
        $id = '';
        if ($session instanceof SessionIdentifierAwareInterface) {
            $id = $session->getId();
        }

        // Regenerate if:
        // - the session is marked as regenerated
        // - the id is empty, but the data has changed (new session)
        if ($session->isRegenerated()
            || ('' === $id && $session->hasChanged())
        ) {
            $id = $this->regenerateSession();
        }

        $_SESSION = $session->toArray();
        session_write_close();

        // If we do not have an identifier at this point, it means a new
        // session was created, but never written to. In that case, there's
        // no reason to provide a cookie back to the user.
        if ('' === $id) {
            return $response;
        }

        $sessionCookie = SetCookie::create(session_name())
            ->withValue($id)
            ->withPath($this->cookiePath);

        if ($this->cookieLifetime > 0) {
            $sessionCookie = $sessionCookie->withExpires(time() + $this->cookieLifetime);
        }

        return FigResponseCookies::set($response, $sessionCookie);
    }

    /**
     * Regenerates the session safely.
     *
     * @link http://php.net/manual/en/function.session-regenerate-id.php (Example #2)
     * @throws \Zend_Session_Exception
     */
    private function regenerateSession(): string
    {
        session_write_close();
        $id = $this->generateSessionId();

        // Zend_Session does not recognize if a session was closed, so we need to start it manually
        session_id($id);
        session_start(['use_strict_mode' => false]);
        return $id;
    }
}
