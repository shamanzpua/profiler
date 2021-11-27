<?php

namespace shamanzpua\Profiler\LogStorages;

use shamanzpua\Profiler\Contracts\ILogStorage;

class NullStorage implements ILogStorage
{

    public function put(string $name, array $logs, $time)
    {
        return null;
    }
}