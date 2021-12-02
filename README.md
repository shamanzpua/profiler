```php
<?php
require_once 'vendor/autoload.php';

use shamanzpua\Profiler\Profiler;
use shamanzpua\Profiler\LogStorages\FileStorage;

$logsPath = "/data/project/logs/profiler/";

Profiler::getInstance()->setLogStorage(new FileStorage($logsPath);

performance_profiling_start("PROFILER_LOG_NAME");
profiler_breakpoint("POINT1");
profiler_breakpoint("POINT2");
performance_profiling_stop('POINT3');

```