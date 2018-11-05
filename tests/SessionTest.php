<?php

declare(strict_types=1);

namespace TobiasTest\Expressive\Zf1Session;

use Tobias\Zend\Expressive\Zf1Session\Session;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;

class SessionTest extends TestCase
{
    /**
     * @var SessionInterface
     */
    private $session;

    private $sessionData;

    protected function setUp()
    {
        $this->sessionData = [];
        $this->session = new Session($this->sessionData);
    }

    public function testSessionIdFromConstructorIsUsed(): void
    {
        $data = [];
        $session = new Session($data, '1234abcd');

        $this->assertSame('1234abcd', $session->getId());
    }

    public function testImplementsSessionInterface(): void
    {
        $this->assertInstanceOf(SessionInterface::class, $this->session);
    }

    public function testIsNotChangedAtInstantiation(): void
    {
        $this->assertFalse($this->session->hasChanged());
    }

    public function testIsNotRegeneratedByDefault(): void
    {
        $this->assertFalse($this->session->isRegenerated());
    }

    public function testRegenerateProducesANewInstance(): SessionInterface
    {
        $regenerated = $this->session->regenerate();
        $this->assertNotSame($this->session, $regenerated);

        return $regenerated;
    }

    /**
     * @depends testRegenerateProducesANewInstance
     *
     * @param SessionInterface $session
     */
    public function testRegeneratedSessionReturnsTrueForIsRegenerated(SessionInterface $session): void
    {
        $this->assertTrue($session->isRegenerated());
    }

    /**
     * @depends testRegenerateProducesANewInstance
     *
     * @param SessionInterface $session
     */
    public function testRegeneratedSessionIsChanged(SessionInterface $session): void
    {
        $this->assertTrue($session->hasChanged());
    }

    public function testSettingDataInSessionMakesItAccessible(): SessionInterface
    {
        $this->assertFalse($this->session->has('foo'));
        $this->session->set('foo', 'bar');
        $this->assertTrue($this->session->has('foo'));
        $this->assertSame('bar', $this->session->get('foo'));

        return $this->session;
    }

    /**
     * @depends testSettingDataInSessionMakesItAccessible
     *
     * @param SessionInterface $session
     */
    public function testSettingDataInSessionChangesSession(SessionInterface $session): void
    {
        $this->assertTrue($session->hasChanged());
    }

    /**
     * @depends testSettingDataInSessionMakesItAccessible
     *
     * @param SessionInterface $session
     */
    public function testToArrayReturnsAllDataPreviouslySet(SessionInterface $session): void
    {
        $this->assertSame(['foo' => 'bar'], $session->toArray());
    }

    /**
     * @depends testSettingDataInSessionMakesItAccessible
     *
     * @param SessionInterface $session
     */
    public function testCanUnsetDataInSession(SessionInterface $session): void
    {
        $session->unset('foo');
        $this->assertFalse($session->has('foo'));
    }

    public function testClearingSessionRemovesAllData(): void
    {
        $original = [
            'foo' => 'bar',
            'baz' => 'bat',
        ];
        $this->session->set('foo', 'bar');
        $this->session->set('baz', 'bat');
        $this->assertSame($original, $this->session->toArray());
        $this->session->clear();
        $this->assertNotSame($original, $this->session->toArray());
        $this->assertSame([], $this->session->toArray());
    }

    public function serializedDataProvider(): iterable
    {
        $data = $expected = (object)['test_case' => $this];
        yield 'nested-objects' => [$data, $expected];
    }

    /**
     * @dataProvider serializedDataProvider
     *
     * @param $data
     * @param $expected
     */
    public function testSetEnsuresDataIsJsonSerializable($data, $expected): void
    {
        $this->session->set('foo', $data);
        $this->assertSame($data, $this->session->get('foo'));
        $this->assertSame($expected, $this->session->get('foo'));
    }

    public function testImplementsSessionIdentifierAwareInterface(): void
    {
        $this->assertInstanceOf(SessionIdentifierAwareInterface::class, $this->session);
    }

    public function testGetIdReturnsEmptyStringIfNoIdentifierProvidedToConstructor(): void
    {
        $this->assertSame('', $this->session->getId());
    }
}
