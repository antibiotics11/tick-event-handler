<?php

namespace antibiotics11\TickEventHandler;
use SplObjectStorage;
use function register_tick_function;
use function unregister_tick_function;
use const PHP_INT_MAX;

class TickEventHandler {
  protected static ?self $handler = null;

  /**
   * Get the TickEventHandler instance.
   *
   * @return TickEventHandler
   */
  public static function getHandler(): self {
    self::$handler ??= new self();
    return self::$handler;
  }

  /**
   * @var SplObjectStorage<TickEvent>
   */
  protected SplObjectStorage $tickEvents;

  /**
   * @var int Current tick count.
   */
  protected int $ticks = 0;

  /**
   * @var bool Whether a tick event is currently running.
   */
  protected bool $tickEventRunning = false;

  /**
   * @var bool Whether the tick handler is registered.
   */
  protected bool $handlerRegistered = false;

  /**
   * Handle tick events.
   *
   * @return void
   */
  public function handle(): void {

    if ($this->tickEventRunning) {
      return;
    }

    $this->ticks++;

    foreach ($this->tickEvents as $tickEvent) {
      if ($this->ticks % $tickEvent->getTick() === 0) {
        $this->tickEventRunning = true;
        $tickEvent->run();
        $this->tickEventRunning = false;
      }
    }

    $this->ticks >= PHP_INT_MAX and $this->ticks = 0;

  }

  /**
   * Check if a TickEvent already exists.
   *
   * @param TickEvent $tickEvent
   * @return bool True if the TickEvent exists, otherwise false.
   */
  public function hasTickEvent(TickEvent $tickEvent): bool {
    return $this->tickEvents->contains($tickEvent);
  }

  /**
   * Add a tick event.
   *
   * @param TickEvent $tickEvent
   * @return bool True if the tick event was added successfully, otherwise false.
   */
  public function addTickEvent(TickEvent $tickEvent): bool {

    $this->tickEvents ??= new SplObjectStorage();

    if ($this->hasTickEvent($tickEvent)) {
      return false;
    }

    $this->tickEvents->attach($tickEvent);

    if (!$this->handlerRegistered) {
      if (!$this->registerTickFunction()) {
        return false;
      }
    }

    return true;

  }

  /**
   * Remove a tick event.
   *
   * @param TickEvent $tickEvent
   * @return bool True if the tick event was removed successfully, otherwise false.
   */
  public function removeTickEvent(TickEvent $tickEvent): bool {

    if (!$this->hasTickEvent($tickEvent) || $tickEvent->isStarted()) {
      return false;
    }

    $this->tickEvents->detach($tickEvent);

    if ($this->tickEvents->count() === 0) {
      $this->unregisterTickFunction();
    }

    return true;

  }

  /**
   * Remove all tick events.
   *
   * @return void
   */
  public function removeAllTickEvents(): void {

    $this->tickEvents->removeAll($this->tickEvents);

    $this->ticks = 0;
    $this->tickEventRunning = false;

    $this->handlerRegistered and $this->unregisterTickFunction();

  }

  protected function registerTickFunction(): bool {
    if (register_tick_function([ $this, "handle" ])) {
      $this->handlerRegistered = true;
      return true;
    }
    return false;
  }

  protected function unregisterTickFunction(): void {
    unregister_tick_function([ $this, "handle" ]);
    $this->handlerRegistered = false;
  }

  private function __construct() {}
  private function __clone() {}

}