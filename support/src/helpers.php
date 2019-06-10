<?php

use Swoft\Support\Assets;
use Swoft\Support\Url;
use Symfony\Component\VarDumper\VarDumper;
use Swoft\App;
use Swoft\Console\Helper\ConsoleUtil;
use Swoft\Support\Arr;
use Swoft\Support\Str;
use Swoft\Support\Collection;
use Swoft\Config\Config;
use Swoft\Bean\BeanFactory;
use Swoft\Support\MessageBag;
use Psr\Http\Message\ResponseInterface;
use Swoft\Support\SessionHelper;
use Swoft\Stdlib\Contract\Arrayable;

use Swoft\Bean\Container;
use Swoft\Http\Message\Request;
use Swoft\Http\Server\Contract\MiddlewareInterface;
use Swoft\Http\Server\Exception\HttpServerException;
use Swoft\Http\Server\RequestHandler;
use Swoft\Http\Server\Router\Route;
use Swoft\Http\Server\Router\Router;

if (!function_exists('class_action')) {
    function class_action()
    {
        $request = \context()->getRequest();
        $uri = $request->getUri();
        $method  = $request->getMethod();
        $uriPath = $request->getUriPath();
        $router        = Container::$instance->getSingleton('httpRouter');
        $routerHandler = $router->match($uriPath, $method);
        [$status, , $route] = $routerHandler;
        $handlerId = $route->getHandler();
        [$className, $action] = explode('@', $handlerId);
        return [$className, $action];
    }
}

if (!function_exists('admin_path')) {
    /**
     * Get admin path.
     *
     * @param string $path
     *
     * @return string
     */
    function admin_path($path = '')
    {
        return ucfirst(config('admin.directory')).($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('admin_url')) {
    /**
     * Get admin url.
     *
     * @param string $path
     * @param mixed  $parameters
     *
     * @return string
     */
    function admin_url($path = '', $parameters = [])
    {
        if (Url::isValidUrl($path)) {
            return $path;
        }

        return Url::to(admin_base_path($path), $parameters);
    }
}

if (!function_exists('admin_base_path')) {
    /**
     * Get admin url.
     *
     * @param string $path
     *
     * @return string
     */
    function admin_base_path($path = '')
    {
        $prefix = '/'.trim(config('admin.route.prefix'), '/');

        $prefix = ($prefix == '/') ? '' : $prefix;

        if ($path === '/') {
            return $prefix ?: $path;
        }

        return $path ? $prefix.'/'.trim($path, '/') : $prefix;
    }
}

if (!function_exists('build404page')) {
    /**
     * 创建404页面
     *
     * @return \Swoft\Blade\Contracts\View
     */
    function build404page()
    {
        $data = [
            'title' => 404,
            'error' => '404 Not Found',
            'message' => 'Sorry, the page you are looking for could not be found.'
        ];
        return blade('admin::partials.error', $data);
    }
}

if (!function_exists('get_admin_notice')) {
    /**
     * @return MessageBag|null
     */
    function get_admin_notice()
    {
        if (Admin::hasContextAttribute('__notice')) {
            return Admin::getContextAttribute('__notice');
        }
        $session = SessionHelper::wrap();

        $notice = $session ? $session->pull('__notice') : null;

        Admin::setContextAttribute('__notice', $notice);

        return $notice;
    }
}

if (!function_exists('admin_notice')) {
    /**
     * Flash a layer message bag to session.
     *
     * @param string $message
     * @param string $type
     * @param string $offset
     */
    function admin_notice($message = '', string $type = 'success', string $offset = 't')
    {
        $notice = new MessageBag(get_defined_vars());

        $session = SessionHelper::wrap();

        $session ? $session->put('__notice', $notice) : null;

        Admin::setContextAttribute('__notice', $notice);
    }
}

if (!function_exists('admin_success')) {

    /**
     * Flash a success message bag to session.
     *
     * @param string $title
     * @param string $message
     */
    function admin_success($title, $message = '')
    {
        admin_info($title, $message, 'success');
    }
}

if (!function_exists('admin_error')) {

    /**
     * Flash a error message bag to session.
     *
     * @param string $title
     * @param string $message
     */
    function admin_error($title, $message = '')
    {
        admin_info($title, $message, 'danger');
    }
}

if (!function_exists('admin_warning')) {

    /**
     * Flash a warning message bag to session.
     *
     * @param string $title
     * @param string $message
     */
    function admin_warning($title, $message = '')
    {
        admin_info($title, $message, 'warning');
    }
}

if (!function_exists('admin_info')) {

    /**
     * Flash a message bag to session.
     *
     * @param string $title
     * @param string $message
     * @param string $type
     */
    function admin_info($title, $message = '', $type = 'info')
    {
        $message = new MessageBag(get_defined_vars());

        $key     = '_admin_msg_';
        $session = SessionHelper::wrap();

        $data = null;
        if ($session) {
            $session->push($key, $message);
            $data = $session->get($key);
        }

        Admin::setContextAttribute($key, $data ?: [$message]);
    }
}

if (!function_exists('admin_flash_message')) {
    /**
     *
     * @return MessageBag[]
     */
    function admin_flash_message()
    {
        $key = '_admin_msg_';

        if (Admin::hasContextAttribute($key)) {
            return Admin::getContextAttribute($key);
        }

        $session = SessionHelper::wrap();

        $message = $session ? $session->pull($key) : [];

        Admin::setContextAttribute($key, $message);

        return $message;
    }
}

if (!function_exists('flash_errors_each')) {
    /**
     * 批量设置
     *
     * @param array $errors
     */
    function flash_errors_each(array $errors) {
        foreach ($errors as $k => &$v) {
            flash_errors($v, $k);
        }
    }
}

if (!function_exists('flash_errors')) {
    /**
     * 存储错误信息
     *
     * @param \Swoft\Support\Contracts\MessageProvider|array|string $provider
     * @param string $key
     * @return $this
     */
    function flash_errors($provider, string $key = 'default')
    {
        $name = '__errors__';

        if ($provider instanceof \Swoft\Support\Contracts\MessageProvider) {
            $value = $provider->getMessageBag();
        } else {
            $value = new MessageBag((array) $provider);
        }

        $session = SessionHelper::wrap();

        $errors = $session->get($name, new \Swoft\Support\ViewErrorBag());

        if (! $errors instanceof \Swoft\Support\ViewErrorBag) {
            $errors = new \Swoft\Support\ViewErrorBag();
        }

        $session->put(
            $name, $errors->put($key, $value)
        );
        Admin::setContextAttribute($name, $errors);
    }
}


if (!function_exists('get_flash_errors')) {
    /**
     * 获取错误消息
     *
     * @return \Swoft\Support\ViewErrorBag
     */
    function get_flash_errors() {
        $key = '__errors__';

//        if (Admin::hasContextAttribute($key)) {
//            return Admin::getContextAttribute($key);
//        }

        $session = SessionHelper::wrap();

        $error = $session ? $session->pull($key) : null;
        $error = $error ?: new \Swoft\Support\ViewErrorBag();

//        Admin::setContextAttribute($key, $error);

        return $error;

    }
}

if (!function_exists('flash_input')) {
    /**
     * 暂存用户输入数据
     *
     * @param array $input
     * @return null|void
     */
    function flash_input(array $input)
    {
        $session = SessionHelper::wrap();

        $session ? $session->put('__input__', $input) : null;

        Admin::setContextAttribute('__input__', $input);
    }
}

if (!function_exists('old_input')) {
    /**
     * 获取用户上次请求的GET/POST参数
     *
     * @param string $key
     * @param null $default
     * @return array|mixed
     */
    function old_input(string $key = null, $default = null)
    {
        if (Admin::hasContextAttribute('__input__')) {
            $input = Admin::getContextAttribute('__input__');
        } else {
            $session = SessionHelper::wrap();
            $input = (array)($session ? $session->pull('__input__') : []);
            Admin::setContextAttribute('__input__', $input);
        }
        if ($key === null) {
            return $input;
        }

        return array_get($input, $key, $default);
    }
}


if (!function_exists('admin_asset')) {
    /**
     * 获取admin后台的静态资源
     *
     * @param $path
     * @return string
     */
    function admin_asset($path)
    {
        return Assets::alias('@admin/'.ltrim($path,'/'));
    }
}

if (!function_exists('array_delete')) {
    /**
     * Delete from array by value.
     *
     * @param array $array
     * @param mixed $value
     */
    function array_delete(&$array, $value)
    {
        foreach ($array as $index => $item) {
            if ($value == $item) {
                unset($array[$index]);
            }
        }
    }
}

if (!function_exists('arr_merge')) {
    /**
     * 合并新的数组到旧的数组
     *
     * @param array $content
     * @param array $new
     * @param bool $recurrence
     * @return array
     */
    function arr_merge(array &$content, array &$new, $recurrence = false)
    {
        foreach ($new as $k => & $v) {
            if ($recurrence) {
                if (isset($content[$k]) && is_array($content[$k]) && is_array($v)) {
                    $content[$k] = arr_merge($content[$k], $v, true);
                    continue;
                }
            }

            $content[$k] = $v;
        }

        return $content;
    }
}

if (!function_exists('object_array')) {
    function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = object_array($value);
            }
        }
        return $array;
    }
}

if (!function_exists('admin_debug')) {
    /**
     * 输入debug日志
     * 非debug环境下无效
     *
     * @param $msg
     * @param $data
     * @param string $type
     */
    function admin_debug($msg, $data = [], string $type = 'debug')
    {
        if (!Admin::isDebug()) {
            return;
        }
        if ($data instanceof \Swoft\Contract\Arrayable) {
            $data = $data->toArray();
        }
        \Swoft\Admin\Debugger\Collector::debug($msg, $data);
        debuglog($msg.' '.json_encode($data), [], $type);
    }
}

if (!function_exists('translate_field')) {
    /**
     * 翻译当前控制器数据表字段名称
     *
     * @param string $column
     * @return mixed
     */
    function translate_field(string $column)
    {
        return Admin::translateField($column);
    }
}

if (!function_exists('translate_label')) {
    /**
     * 翻译当前控制器labels字段
     *
     * @param string $column
     * @return mixed
     */
    function translate_label(string $column)
    {
        return Admin::translateLabel($column);
    }
}


if (!function_exists('is_pjax_request')) {
    /**
     * 判断是否是pjax请求
     *
     * @return bool
     */
    function is_pjax_request()
    {
        $request = \Swoft\Core\RequestContext::getRequest();

        return (bool)isset($request->getHeader('X-PJAX')[0]) ? $request->getHeader('X-PJAX')[0] : false;
    }
}

if (!function_exists('is_ajax_request')) {
    /**
     * 判断是否是ajax请求
     *
     * @return bool
     */
    function is_ajax_request()
    {
        $request = \Swoft\Core\RequestContext::getRequest();

        $xq = isset($request->getHeader('X-Requested-With')[0]) ? $request->getHeader('X-Requested-With')[0] : '';

        return 'XMLHttpRequest' === $xq;
    }
}

if (! function_exists('append_config')) {
    /**
     * Assign high numeric IDs to a config item to force appending.
     *
     * @param  array  $array
     * @return array
     */
    function append_config(array $array)
    {
        $start = 9999;

        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $start++;

                $array[$start] = Arr::pull($array, $key);
            }
        }

        return $array;
    }
}

if (! function_exists('array_add')) {
    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function array_add($array, $key, $value)
    {
        return Arr::add($array, $key, $value);
    }
}

if (! function_exists('array_collapse')) {
    /**
     * Collapse an array of arrays into a single array.
     *
     * @param  array  $array
     * @return array
     */
    function array_collapse($array)
    {
        return Arr::collapse($array);
    }
}

if (! function_exists('array_divide')) {
    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array  $array
     * @return array
     */
    function array_divide($array)
    {
        return Arr::divide($array);
    }
}

if (! function_exists('array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    function array_dot($array, $prepend = '')
    {
        return Arr::dot($array, $prepend);
    }
}

if (! function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    function array_except($array, $keys)
    {
        return Arr::except($array, $keys);
    }
}

if (! function_exists('array_first')) {
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    function array_first($array, callable $callback = null, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}

if (! function_exists('array_flatten')) {
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @param  int  $depth
     * @return array
     */
    function array_flatten($array, $depth = INF)
    {
        return Arr::flatten($array, $depth);
    }
}

if (! function_exists('array_forget')) {
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return void
     */
    function array_forget(&$array, $keys)
    {
        return Arr::forget($array, $keys);
    }
}

if (! function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (! function_exists('array_has')) {
    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|array  $keys
     * @return bool
     */
    function array_has($array, $keys)
    {
        return Arr::has($array, $keys);
    }
}

if (! function_exists('array_last')) {
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    function array_last($array, callable $callback = null, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }
}

if (! function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    function array_only($array, $keys)
    {
        return Arr::only($array, $keys);
    }
}

if (! function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array.
     *
     * @param  array   $array
     * @param  string|array  $value
     * @param  string|array|null  $key
     * @return array
     */
    function array_pluck($array, $value, $key = null)
    {
        return Arr::pluck($array, $value, $key);
    }
}

if (! function_exists('array_prepend')) {
    /**
     * Push an item onto the beginning of an array.
     *
     * @param  array  $array
     * @param  mixed  $value
     * @param  mixed  $key
     * @return array
     */
    function array_prepend($array, $value, $key = null)
    {
        return Arr::prepend($array, $value, $key);
    }
}

if (! function_exists('array_pull')) {
    /**
     * Get a value from the array, and remove it.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function array_pull(&$array, $key, $default = null)
    {
        return Arr::pull($array, $key, $default);
    }
}

if (! function_exists('array_random')) {
    /**
     * Get a random value from an array.
     *
     * @param  array  $array
     * @param  int|null  $num
     * @return mixed
     */
    function array_random($array, $num = null)
    {
        return Arr::random($array, $num);
    }
}

if (! function_exists('array_set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }
}

if (! function_exists('array_sort')) {
    /**
     * Sort the array by the given callback or attribute name.
     *
     * @param  array  $array
     * @param  callable|string|null  $callback
     * @return array
     */
    function array_sort($array, $callback = null)
    {
        return Arr::sort($array, $callback);
    }
}

if (! function_exists('array_sort_recursive')) {
    /**
     * Recursively sort an array by keys and values.
     *
     * @param  array  $array
     * @return array
     */
    function array_sort_recursive($array)
    {
        return Arr::sortRecursive($array);
    }
}

if (! function_exists('array_where')) {
    /**
     * Filter the array using the given callback.
     *
     * @param  array  $array
     * @param  callable  $callback
     * @return array
     */
    function array_where($array, callable $callback)
    {
        return Arr::where($array, $callback);
    }
}

if (! function_exists('array_wrap')) {
    /**
     * If the given value is not an array, wrap it in one.
     *
     * @param  mixed  $value
     * @return array
     */
    function array_wrap($value)
    {
        return Arr::wrap($value);
    }
}

if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed  $value
     * @return Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if (! function_exists('data_fill')) {
    /**
     * Fill in data where it's missing.
     *
     * @param  mixed   $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @return mixed
     */
    function data_fill(&$target, $key, $value)
    {
        return data_set($target, $key, $value, false);
    }
}

if (! function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed   $target
     * @param  string|array  $key
     * @param  mixed   $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (! is_array($target)) {
                    return value($default);
                }

                $result = Arr::pluck($target, $key);

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (! function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param  mixed  $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (! Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (! Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || ! Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (! isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || ! isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}

if (! function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('html_js')) {
    /**
     * @param string|array $path
     * @return string
     */
    function html_js($path)
    {
        if (!$path) {
            return '';
        }
        if (is_array($path) || is_iterable($path)) {
            $html = '';
            foreach ($path as &$v) {
                $html .= html_js($v);
            }
            return $html;
        }
        $path = Assets::js($path);
        return "<script src=\"{$path}\"></script>";
    }
}

if (!function_exists('html_css')) {
    /**
     * @param string|array $path
     * @return string
     */
    function html_css($path)
    {
        if (!$path) {
            return '';
        }
        if (is_array($path) || is_iterable($path)) {
            $html = '';
            foreach ($path as &$v) {
                $html .= html_css($v);
            }
            return $html;
        }
        $path = Assets::css($path);
        return "<link href=\"{$path}\" rel=\"stylesheet\" type=\"text/css\" />";
    }
}

if (!function_exists('assets_alias')) {
    /**
     * 解析静态资源路径别名
     *
     * @param string $path
     * @return string
     */
    function assets_alias(string $path)
    {
        return Assets::alias($path);
    }
}

if (!function_exists('is_valid_url')) {
    /**
     * Determine if the given path is a valid URL.
     *
     * @param  string $path
     * @return bool
     */
    function is_valid_url(string $path)
    {
        return Url::isValidUrl($path);
    }
}

if (!function_exists('redirect_to')) {
    /**
     * Create a new redirect response to the given path.
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @param  bool    $secure
     * @return \Psr\Http\Message\ResponseInterface
     */
    function redirect_to(string $path, $status = 302, $headers = [], $secure = null)
    {
        return \Swoft\Support\Redirector::to($path, $status, $headers, $secure);
    }
}

if (!function_exists('redirect_refresh')) {
    /**
     * Create a new redirect response to the current URI.
     *
     * @param  int    $status
     * @param  array  $headers
     * @return \Psr\Http\Message\ResponseInterface
     */
    function redirect_refresh(int $status = 302, array $headers = [])
    {
        return \Swoft\Support\Redirector::refresh($status, $headers);
    }
}

if (!function_exists('redirect_back')) {
    /**
     * Create a new redirect response to the previous location.
     *
     * @param  int    $status
     * @param  array  $headers
     * @param  mixed  $fallback
     * @return \Psr\Http\Message\ResponseInterface
     */
    function redirect_back($status = 302, $headers = [], $fallback = false)
    {
        return \Swoft\Support\Redirector::back($status, $headers, $fallback);
    }
}

if (!function_exists('ddd')) {
    /**
     * @param $var
     * @param array ...$moreVars
     * @return \Psr\Http\Message\ResponseInterface
     */
    function ddd($var, ...$moreVars)
    {
        VarDumper::setHandler(function ($data) {
            $cloner = new \Symfony\Component\VarDumper\Cloner\VarCloner();
            $dumper = new \Symfony\Component\VarDumper\Dumper\HtmlDumper();
            $dumper->dump($cloner->cloneVar($data));
        });

        ob_start();
        VarDumper::dump($var);

        foreach ($moreVars as $var) {
            VarDumper::dump($var);
        }

        $content = ob_get_contents();
        ob_end_clean();

        return html_response($content);
    }
}

if (!function_exists('debuglog')) {
    /**
     * 输出日志到命令行控制台
     *
     * @param string $msg
     * @param array|object $data
     * @param string $type
     */
    function debuglog($msg, $data = [], string $type = 'debug')
    {
        $daemonize = App::getAppProperties()->get('server.setting.daemonize');
        if ($daemonize == 1 || !config('debug')) {
            return;
        }
        ConsoleUtil::log($msg, [], $type);
        if ($data) {
            VarDumper::dump($data);
        }
    }
}

if (!function_exists('consolelog')) {
    /**
     * 打印console日志
     *
     * @param array ...$params
     */
    function consolelog(...$params)
    {
        $daemonize = \Swoft::getBean('config')->get('server.setting.daemonize');
//        $daemonize = App::getAppProperties()->get('server.setting.daemonize');
        if ($daemonize == 1 || !config('debug')) {
            return;
        }
        foreach ($params as $data) {
            VarDumper::dump($data);
        }
    }
}

if (!function_exists('html_response')) {
    /**
     * @param string|\Psr\Http\Message\ResponseInterface $content
     * @param string $charset
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    function html_response($content, string $charset = 'utf-8', \Psr\Http\Message\ResponseInterface $response = null)
    {
        if ($content instanceof \Psr\Http\Message\ResponseInterface) {
            return $content;
        }
        if ($content instanceof \Swoft\Support\Contracts\Htmlable) {
            $content = $content->toHtml();
        } elseif ($content instanceof \Swoft\Support\Contracts\Renderable) {
            $content = $content->render();
        }
        $response = $response ?: \context()->getResponse();

        return $response->withContent($content)
            ->withoutHeader('Content-Type')
            ->withAddedHeader('Content-Type', 'text/html')
            ->withAddedHeader('Content-Type', 'charset='.$charset);
    }
}

if (!function_exists('trans_option')) {
    /**
     * @param string|int $value 选项值
     * @param string $column 字段名称
     * @param string $filename 翻译文件的文件名
     * @return mixed
     */
    function trans_option($value, string $column, string $filename = '')
    {
        $options = \Swoft\Support\Translator::make()->translate($column, $filename.'.options');

        return isset($options[$value]) ? $options[$value] : $value;
    }
}

if (! function_exists('t')) {
    /**
     * 翻译
     * 如果指定的翻译文件中没有该翻译,则会读取默认的翻译文件翻译
     *
     * @param string $content 要翻译的字段
     * @param string $type 翻译类别，比如xxx.xx/xx （文件名/文件名.子类别）
     * @param array  $params   参数
     * @param string $language 当前语言环境
     * @return mixed
     */
    function t($content, string $type = '', array $params = [], string $language = null)
    {
        return \Swoft\Support\Translator::make()->translate($content, $type, $params, $language);
    }
}

if (!function_exists('current_lang')) {
    /**
     * 获取当前语言包
     *
     * @return string
     */
    function current_lang()
    {
        return \Swoft\Support\Translator::make()->current();
    }
}

if (!function_exists('str__slug')) {
    /**
     * 大写字母为小写中划线或其他字符
     *
     * @param string $name
     * @param string $symbol
     * @return mixed
     */
    function str__slug(string $name, string $symbol = '-')
    {
        $text = preg_replace_callback('/([A-Z])/', function (& $text) use ($symbol) {
            return $symbol . strtolower($text[1]);
        }, $name);

        return str_replace('_', $symbol, ltrim($text, $symbol));
    }
}

if (!function_exists('camel__case')) {
    /**
     * 下划线命名转化为驼峰
     *
     * @param $name
     * @param string $symbol
     * @return mixed|string
     */
    function camel__case($name, $symbol = '_')
    {
        return preg_replace_callback("/{$symbol}([a-zA-Z])/", function (&$matches) {
            return ucfirst($matches[1]);
        }, $name);
    }
}

if (!function_exists('http_input')) {
    /**
     * 获取POST或GET参数
     *
     * @param string|null $key
     * @param mixed $def
     * @return mixed
     */
    function http_input(string $key = null, $def = null)
    {
        return \Swoft\Support\Input::request($key, $def);
    }
}

if (!function_exists('http_get')) {
    /**
     * 获取GET参数
     *
     * @param string $key
     * @param mixed $def
     * @return mixed
     */
    function http_get(string $key = null, $def = null)
    {
        return \Swoft\Support\Input::get($key, $def);
    }
}

if (!function_exists('http_post')) {
    /**
     * 获取POST参数
     *
     * @param string $key
     * @param mixed $def
     * @return mixed
     */
    function http_post($key = null, $def = null)
    {
        return \Swoft\Support\Input::post($key, $def);
    }
}

if (!function_exists('url_query')) {
    /**
     * @param $url
     * @param array $newQuery
     * @return \Swoft\Support\UrlQuery
     */
    function url_query($url = null, array $newQuery = [])
    {
        return Url::query($url, $newQuery);
    }
}

if (!function_exists('export_array')) {
    /**
     * 把php数据转化成文本形式
     *
     * @param array $array
     * @param int   $level
     * @return string
     */
    function export_array(array &$array, $level = 1)
    {
        $start = '[';
        $end   = ']';

        $txt = "$start\n";

        foreach ($array as $k => & $v) {
            if (is_array($v)) {
                $pre = is_string($k) ? "'$k' => " : "$k => ";

                $txt .= str_repeat(' ', $level * 4) . $pre . export_array($v, $level + 1) . ",\n";

                continue;
            }
            $t = $v;

            if ($v === true) {
                $t = 'true';
            } elseif ($v === false) {
                $t = 'false';
            } elseif ($v === null) {
                $t = 'null';
            } elseif (is_string($v)) {
                $v = str_replace("'", "\\'", $v);
                $t = "'$v'";
            }

            $pre = is_string($k) ? "'$k' => " : "$k => ";

            $txt .= str_repeat(' ', $level * 4). "{$pre}{$t},\n";
        }

        return $txt . str_repeat(' ', ($level - 1) * 4) . $end;
    }
}

if (!function_exists('export_array_php')) {
    /**
     * 把php数据转化成文本形式，并以"return"形式返回
     *
     * @param array $array
     * @return string
     */
    function export_array_php(array &$array)
    {
        return "<?php \nreturn " . export_array($array) . ";\n";
    }
}

if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed  $value
     * @param  callable  $callback
     * @return mixed
     */
    function tap($value, Closure $callback)
    {
        $callback($value);

        return $value;
    }
}

if (! function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    function array_except($array, $keys)
    {
        return \Swoft\Support\Arr::except($array, $keys);
    }
}

if (! function_exists('e')) {
    /**
     * Escape HTML special characters in a string.
     *
     * @param  \Swoft\Support\Contracts\Htmlable|string  $value
     * @param  bool  $doubleEncode
     * @return string
     */
    function e($value, $doubleEncode = true)
    {
        if ($value instanceof \Swoft\Support\Contracts\Htmlable) {
            return $value->toHtml();
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

if (! function_exists('blade')) {
    /**
     * blade模板引擎
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return \Swoft\Blade\Contracts\View
     */
    function blade(string $view, array $data = [], $mergeData = [])
    {
//        consolelog(\Swoft\Bean\BeanFactory::getContainer());
//        $res = \Swoft\Bean\BeanFactory::getContainer();
//        consolelog($res);
//        file_put_contents("./tetst.txt",$res);
//        consolelog(BeanFactory::getBean("blade"));
        return \bean('blade_view')->make($view, $data, $mergeData);
    }
}

if (! function_exists('blade_factory')) {
    /**
     * blade模板引擎工厂对象
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return \Swoft\Blade\Factory
     */
    function blade_factory()
    {
//        consolelog(\bean("blade_view"));
//        consolelog(BeanFactory::getBean("view.engine.resolver"));
        return \bean('blade_view');
    }
}

if (! function_exists('filesystem')) {
    /**
     * @return \Swoft\Support\Filesystem
     */
    function filesystem(): \Swoft\Support\Filesystem
    {
        static $instance;

        return $instance ?: ($instance = new \Swoft\Support\Filesystem);
    }
}
