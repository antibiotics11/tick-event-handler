<?php

namespace antibiotics11\TickManager;
use InvalidArgumentException;
use RuntimeException;
use function register_tick_function, unregister_tick_function;
use function count, in_array, array_values, array_key_last;
use const PHP_INT_MAX;

class TickManager {

  protected static self $handler;
  public static function getManager(): self {
    self::$handler ??= new self();
    return self::$handler;
  }

  /** @var TickHandler[][] */
  protected array $handlers                 = [];
  protected int   $currentTick              = 0;
  protected bool  $isHandlerRunning         = false;
  protected bool  $isTickFunctionRegistered = false;

  protected function registerTickFunction(): void {
    if (!$this->isTickFunctionRegistered) {
      if (!register_tick_function([ $this, "handle" ])) {
        throw new RuntimeException("register_tick_function() failed.");
      }
      $this->isTickFunctionRegistered = true;
    }
  }

  protected function unregisterTickFunction(): void {
    if ($this->isTickFunctionRegistered) {
      unregister_tick_function([ $this, "handle" ]);
      $this->isTickFunctionRegistered = false;
    }
  }

  /**
   * Handle the execution of registered handlers based on their scheduled ticks.
   *
   * @return void
   */
  public function handle(): void {

    if ($this->isHandlerRunning) {
      return;
    }

    $this->currentTick++;

    foreach ($this->handlers as $tick => $handlers) {
      if ($this->currentTick % $tick === 0) {
        foreach ($handlers as $handler) {
          $this->isHandlerRunning = true;
          $handler->execute();
          if ($handler->isFinished() || $handler->isIdle()) {
            $this->isHandlerRunning = false;
          }
        }
      }
    }

    $this->currentTick >= PHP_INT_MAX and $this->currentTick = 0;

  }

  /**
   * Check if a handler is scheduled for a specific tick.
   *
   * @param int $tick the tick to check.
   * @param TickHandler $tickHandler the handler to check.
   * @return bool
   */
  public function hasHandler(int $tick, TickHandler $tickHandler): bool {
    return in_array($tickHandler, $this->handlers[$tick] ?? []);
  }

  /**
   * Add a handler to be executed at a specified tick.
   *
   * @param int $tick the tick at which to execute the handler.
   * @param TickHandler $tickHandler the handler to add.
   * @return void
   * @throws InvalidArgumentException if the handler already exists.
   * @throws RuntimeException if register_tick_function() fails.
   */
  public function addHandler(int $tick, TickHandler $tickHandler): void {

    if ($this->hasHandler($tick, $tickHandler)) {
      throw new InvalidArgumentException("Handler already exists.");
    }

    $this->handlers[$tick] ??= [];
    $this->handlers[$tick][] = $tickHandler;

    $this->isTickFunctionRegistered or $this->registerTickFunction();

  }

  /**
   * Remove a handler from execution at a specified tick.
   *
   * @param int $tick the tick from which to remove the handler.
   * @param TickHandler|null $tickHandler the handler to remove. if null, remove the last handler for the tick.
   * @return void
   */
  public function removeHandler(int $tick, ?TickHandler $tickHandler = null): void {

    $targetHandlerKey = array_key_last($this->handlers[$tick]);
    if ($targetHandlerKey === null) {
      $this->removeHandlers($tick);
      return;
    }

    if ($tickHandler instanceof TickHandler) {
      foreach ($this->handlers[$tick] as $key => $handler) {
        if ($handler === $tickHandler) {
          $targetHandlerKey = $key;
          break;
        }
      }
    }

    unset($this->handlers[$tick][$targetHandlerKey]);
    $this->handlers[$tick] = array_values($this->handlers[$tick]);

    count($this->handlers[$tick]) == 0 and $this->removeHandlers($tick);

  }

  /**
   * Remove all handlers from execution at a specified tick.
   *
   * @param int $tick
   * @return void
   */
  public function removeHandlers(int $tick): void {
    unset($this->handlers[$tick]);
    count($this->handlers) == 0 and $this->unregisterTickFunction();
  }

  private function __construct() {}
  private function __clone(): void {}

}