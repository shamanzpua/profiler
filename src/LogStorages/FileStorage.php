<?php

namespace shamanzpua\Profiler\LogStorages;

use shamanzpua\Profiler\Contracts\ILogStorage;

class FileStorage implements ILogStorage
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;

        $this->ensureDirectory($path);
    }

    private function ensureDirectory($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * @param string $name
     * @param array $logs
     * @param $time
     * @return void
     */
    public function put(string $name, array $logs, $time)
    {
        $fileName = $name . ".time:" . $time . ".log";
        $this->ensureDirectory($this->path . $name);
        file_put_contents(($this->path . $name ."/". $fileName), serialize($logs));
    }
}