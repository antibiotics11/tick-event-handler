# tick-manager
A PHP library for tick handling.

```php
use antibiotics11\TickManager\{TickHandler, TickManager};

declare(ticks = 1);

$start = time();

// Registering a custom handler to run every 1 tick.
TickManager::getManager()->addHandler(1, new TickHandler(function (&$start): void {

  if (time() - $start >= 10) {
    printf("10 seconds have passed\r\n");
    exit(0);
  }
  
}, $start));

while (true) {
  printf("Hello, world!\r\n");
  sleep(1);
}

```

## Requirements

- PHP >= 8.1

## Installation

```shell
composer require antibiotics11/tick-manager
```
