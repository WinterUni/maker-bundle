<?php

namespace Symfony\Bundle\MakerBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\EventRegistry;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class EventRegistryTest extends TestCase
{
    public function testGetEventClassNameReturnsType()
    {
        $eventObj = new DummyEvent();
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('getListeners')
            ->with('foo.bar')
            ->willReturn([
                'someFunctionToSkip',
                [$eventObj, 'methodNoArg'],
                [$eventObj, 'methodNoType'],
                [$eventObj, 'methodWithType'],
            ]);

        $registry = new EventRegistry($dispatcher);
        $this->assertSame(GetResponseForExceptionEvent::class, $registry->getEventClassName('foo.bar'));
    }

    public function testGetEventClassNameReturnsNoType()
    {
        $eventObj = new DummyEvent();
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('getListeners')
            ->with('foo.bar')
            ->willReturn([
                'someFunctionToSkip',
                [$eventObj, 'methodNoArg'],
                [$eventObj, 'methodNoType'],
            ]);

        $registry = new EventRegistry($dispatcher);
        $this->assertNull($registry->getEventClassName('foo.bar'));
    }

    public function testGetOldEventClassNameFromStandardList()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())
            ->method('getListeners');

        $registry = new EventRegistry($dispatcher);
        $this->assertSame(ConsoleCommandEvent::class, $registry->getEventClassName('console.command'));
    }

    public function testGetNewEventClassNameFromStandardList()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())
                   ->method('getListeners');

        $registry = new EventRegistry($dispatcher);
        $this->assertSame(ExceptionEvent::class, $registry->getEventClassName(ExceptionEvent::class));
    }
}

class DummyEvent extends Event
{
    public function methodNoArg()
    {
    }

    public function methodNoType($event)
    {
    }

    public function methodWithType(GetResponseForExceptionEvent $event)
    {
    }
}
