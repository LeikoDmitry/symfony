<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Proxy;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\StrictSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

/**
 * Tests for SessionHandlerProxy class.
 *
 * @author Drak <drak@zikula.org>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SessionHandlerProxyTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\Matcher
     */
    private $mock;

    /**
     * @var SessionHandlerProxy
     */
    private $proxy;

    protected function setUp(): void
    {
        $this->mock = $this->createMock(\SessionHandlerInterface::class);
        $this->proxy = new SessionHandlerProxy($this->mock);
    }

    protected function tearDown(): void
    {
        $this->mock = null;
        $this->proxy = null;
    }

    public function testOpenTrue()
    {
        $this->mock->expects($this->once())
            ->method('open')
            ->willReturn(true);

        $this->assertFalse($this->proxy->isActive());
        $this->proxy->open('name', 'id');
        $this->assertFalse($this->proxy->isActive());
    }

    public function testOpenFalse()
    {
        $this->mock->expects($this->once())
            ->method('open')
            ->willReturn(false);

        $this->assertFalse($this->proxy->isActive());
        $this->proxy->open('name', 'id');
        $this->assertFalse($this->proxy->isActive());
    }

    public function testClose()
    {
        $this->mock->expects($this->once())
            ->method('close')
            ->willReturn(true);

        $this->assertFalse($this->proxy->isActive());
        $this->proxy->close();
        $this->assertFalse($this->proxy->isActive());
    }

    public function testCloseFalse()
    {
        $this->mock->expects($this->once())
            ->method('close')
            ->willReturn(false);

        $this->assertFalse($this->proxy->isActive());
        $this->proxy->close();
        $this->assertFalse($this->proxy->isActive());
    }

    public function testRead()
    {
        $this->mock->expects($this->once())
            ->method('read')
            ->willReturn('foo')
        ;

        $this->proxy->read('id');
    }

    public function testWrite()
    {
        $this->mock->expects($this->once())
            ->method('write')
            ->willReturn(true)
        ;

        $this->assertTrue($this->proxy->write('id', 'data'));
    }

    public function testDestroy()
    {
        $this->mock->expects($this->once())
            ->method('destroy')
            ->willReturn(true)
        ;

        $this->assertTrue($this->proxy->destroy('id'));
    }

    public function testGc()
    {
        $this->mock->expects($this->once())
            ->method('gc')
            ->willReturn(1)
        ;

        $this->proxy->gc(86400);
    }

    public function testValidateId()
    {
        $mock = $this->createMock(TestSessionHandler::class);
        $mock->expects($this->once())
            ->method('validateId');

        $proxy = new SessionHandlerProxy($mock);
        $proxy->validateId('id');

        $this->assertTrue($this->proxy->validateId('id'));
    }

    public function testUpdateTimestamp()
    {
        $mock = $this->createMock(TestSessionHandler::class);
        $mock->expects($this->once())
            ->method('updateTimestamp')
            ->willReturn(false);

        $proxy = new SessionHandlerProxy($mock);
        $proxy->updateTimestamp('id', 'data');

        $this->mock->expects($this->once())
            ->method('write')
            ->willReturn(true)
        ;

        $this->proxy->updateTimestamp('id', 'data');
    }

    /**
     * @dataProvider provideNativeSessionStorageHandler
     */
    public function testNativeSessionStorageSaveHandlerName($handler)
    {
        $this->assertSame('files', (new NativeSessionStorage([], $handler))->getSaveHandler()->getSaveHandlerName());
    }

    public function provideNativeSessionStorageHandler()
    {
        return [
            [new \SessionHandler()],
            [new StrictSessionHandler(new \SessionHandler())],
            [new SessionHandlerProxy(new StrictSessionHandler(new \SessionHandler()))],
        ];
    }
}

abstract class TestSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface
{
}
