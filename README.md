# tick-event-handler
A utility class for handling tick events in PHP applications.

```php
use antibiotics11\TickEventHandler\TickEvent;
use antibiotics11\TickEventHandler\TickEventHandler;

declare(ticks = 1);

$start = time();

$tickEventHandler = TickEventHandler::getHandler();
$tickEventHandler->addTickEvent(new TickEvent(1, function (&$start): void {

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
