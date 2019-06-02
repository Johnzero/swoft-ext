<?php

namespace Swoft\Blade\Engines;

use Swoft\Blade\Contracts\Engine;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class FileEngine
 * @package Swoft\Blade\Engines
 * @Bean()
 */
class FileEngine implements Engine
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        return file_get_contents($path);
    }
}
