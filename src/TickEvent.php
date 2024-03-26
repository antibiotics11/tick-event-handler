<?php

namespace antibiotics11\TickEventHandler;

class TickEvent {

  /**
   * @var callable Task to be executed on tick.
   */
  protected $task;

  /**
   * @var array<mixed> Arguments for the task.
   */
  protected array $args;

  /**
   * @var mixed|null Result of the task execution.
   */
  protected mixed $result;

  /**
   * @var int Current state of the TickEvent.
   */
  protected int $state;

  protected const STATE_IDLE     = 0;
  protected const STATE_RUNNING  = 1;
  protected const STATE_FINISHED = 2;

  /**
   * @param int $tick The tick count for the event.
   * @param callable $task The task to be executed.
   * @param mixed ...$args Optional arguments for the task.
   */
  public function __construct(protected readonly int $tick, callable $task, ... $args) {
    $this->task   = $task;
    $this->args   = $args;
    $this->result = null;
    $this->state  = self::STATE_IDLE;
  }

  /**
   * Get the tick count for the event.
   *
   * @return int
   */
  public function getTick(): int {
    return $this->tick;
  }

  /**
   * Get the result of the task execution.
   *
   * @return mixed
   */
  public function getResult(): mixed {
    return $this->result;
  }

  /**
   * Execute the task.
   *
   * @return void
   */
  public function run(): void {
    $this->state  = self::STATE_RUNNING;
    $this->result = ($this->task)(... $this->args);
    $this->state  = self::STATE_FINISHED;
  }

  /**
   * Check if the TickEvent is idle.
   *
   * @return bool
   */
  public function isIdle(): bool {
    return $this->state === self::STATE_IDLE;
  }

  /**
   * Check if the TickEvent is started/running.
   *
   * @return bool
   */
  public function isStarted(): bool {
    return $this->state === self::STATE_RUNNING;
  }

  /**
   * Check if the TickEvent is finished.
   *
   * @return bool
   */
  public function isFinished(): bool {
    return $this->state === self::STATE_FINISHED;
  }

}