<?php

//use Swoft\Admin\Admin;
//use Swoft\Support\MessageBag;
//use Swoft\Support\Url;
//use Swoft\Support\Assets;
//use Psr\Http\Message\ResponseInterface;
//use Swoft\Support\SessionHelper;

if (!function_exists('admin_path')) {

    function admin_path($path = '')
    {
        return ucfirst(config('admin.directory')).($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

