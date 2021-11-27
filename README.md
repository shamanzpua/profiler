```php
<?php
require_once 'vendor/autoload.php';

use shamanzpua\Profiler\Profiler;
use shamanzpua\Profiler\LogStorages\FileStorage;

$logsPath = "/data/project/logs/profiler/";

Profiler::getInstance()->setLogStorage(new FileStorage($logsPath);

profileStart("PROFILER_LOG_NAME");
profile("POINT1");
profile("POINT2");
profileEnd('POINT3');

```