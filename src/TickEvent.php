<?php

namespace antibiotics11\TickEventHandler;

class TickEvent {

  protected       $task;
  protected array $args;
  protected mixed $result;
  protected bool  $started;
  protected bool  $finished;

  public function __construct(private readonly int $tick, callable $task, ... $args) {
    $this->task     = $task;
    $this->args     = $args;
    $this->result   = null;
    $this->started  = false;
    $this->finished = false;
  }

  public function getTick(): int {
    return $this->tick;
  }

  public function getResult(): mixed {
    return $this->result;
  }

  public function run(): void {
    $this->started  = true;
    $this->finished = false;
    $this->result   = ($this->task)(... $this->args);
    $this->started  = false;
    $this->finished = true;
  }

  public function isStarted(): bool {
    return $this->started;
  }

  public function isFinished(): bool {
    return $this->finished;
  }

}