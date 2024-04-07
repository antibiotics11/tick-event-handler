<?php

use PHPUnit\Framework\TestCase;
use antibiotics11\TickManager\{TickHandler, TickManager};

class TickManagerTest extends TestCase {

  public function testAddHandler(): void {

    $tick = 1;
    $tickHandler = $this->getMockBuilder(TickHandler::class)
                        ->setConstructorArgs([ fn() => true ])
                        ->getMock();

    $tickManager = TickManager::getManager();
    $tickManager->addHandler($tick, $tickHandler);

    $this->assertTrue($tickManager->hasHandler($tick, $tickHandler));

  }

  public function testRemoveHandler(): void {

    $tick = 1;
    $tickHandler = $this->getMockBuilder(TickHandler::class)
                        ->setConstructorArgs([ fn() => true ])
                        ->getMock();

    $tickManager = TickManager::getManager();
    $tickManager->addHandler($tick, $tickHandler);
    $tickManager->removeHandler($tick, $tickHandler);

    $this->assertFalse($tickManager->hasHandler($tick, $tickHandler));

  }

  public function testHandle(): void {

    $tick = 1;
    $tickHandler = $this->getMockBuilder(TickHandler::class)
                        ->setConstructorArgs([ fn() => true ])
                        ->getMock();
    $tickHandler->expects($this->once())->method("execute");

    $tickManager = TickManager::getManager();
    $tickManager->addHandler($tick, $tickHandler);

    $tickManager->handle();

  }

}