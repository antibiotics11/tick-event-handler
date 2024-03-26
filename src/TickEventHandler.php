<?php

namespace antibiotics11\TickEventHandler;
use SplObjectStorage;
use function register_tick_function;
use function unregister_tick_function;
use const PHP_INT_MAX;

class TickEventHandler {

  private static self $handler;

  /**
   * @return TickEventHandler
   */
  public static function getHandler(): self {
    self::$handler ??= new self();
    return self::$handler;
  }

  /**
   * @var SplObjectStorage<TickEvent>
   */
  private SplObjectStorage $tickEvents;

  /**
   * @var int Current tick count
   */
  private int $ticks = 0;

  /**
   * @var bool Whether a tick event is currently running
   */
  private bool $tickEventRunning = false;

  /**
   * @var bool Whether the tick handler is registered
   */
  private bool $handlerRegistered = false;

  /**
   * Handle tick events.
   *
   * @return void
   */
  public function handle(): void {

    // If a tick event is currently running, return to avoid further processing.
    if ($this->tickEventRunning) {
      return;
    }
    $this->ticks++;

    foreach ($this->tickEvents as $tickEvent) {
      if ($this->ticks % $tickEvent->getTick() == 0) {
        $this->tickEventRunning = true;
        $tickEvent->run();
        $this->tickEventRunning = false;
      }
    }

    $this->ticks >= PHP_INT_MAX and $this->ticks = 0;

  }

  /**
   * Add a tick event.
   *
   * @param TickEvent $tickEvent
   * @return void
   */
  public function addTickEvent(TickEvent $tickEvent): void {

    $this->tickEvents ??= new SplObjectStorage();
    $this->tickEvents->attach($tickEvent);

    if (!$this->handlerRegistered) {
      register_tick_function([ $this, "handle" ]);
      $this->handlerRegistered = true;
    }

  }

  /**
   * Clear all tick events.
   *
   * @return void
   */
  public function clearTickEvents(): void {

    $this->tickEvents->removeAll(new SplObjectStorage());

    $this->ticks = 0;
    $this->tickEventRunning = false;

    if ($this->handlerRegistered) {
      unregister_tick_function([ $this, "handle" ]);
      $this->handlerRegistered = false;
    }

  }

  private function __construct() {}
  private function __clone() {}

}