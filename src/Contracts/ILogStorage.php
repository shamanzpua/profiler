<?php

namespace shamanzpua\Profiler\Contracts;

interface ILogStorage
{
    /**
     * @param string $name
     * @param array $logs
     * @param $time
     * @return mixed
     */
    public function put(string $name, array $logs, $time);
}