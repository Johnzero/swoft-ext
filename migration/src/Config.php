<?php

namespace Swoft\Migrations;

use Swoft\App;
use Swoft\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;

class Config
{
    const DEFAULT_ENV = 'public';

    /**
     * 默认配置文件
     *
     * @var string
     */
    protected static $defaultPath = '@root/config/migration/default.php';

    /**
     * 配置文件模板
     *
     * @var string
     */
    protected static $stub = 'default-config';

    /**
     * 配置信息
     *
     * @var array
     */
    protected static $attributes = [];

    /**
     * 获取配置参数
     *
     * @param string|null $key
     * @param string $path 指定配置文件路径
     * @return mixed
     */
    public static function get(string $key = null, string $path = null)
    {
        $config = static::load($path);

        if ($key === null) {
            return $config;
        }
        return array_get($config, $key);
    }

    /**
     * 获取数据库配置
     *
     * @param string $path
     * @return mixed
     */
    public static function getDatabase(string $path = null)
    {
        return static::get('environments.'.static::DEFAULT_ENV, $path);
    }

    /**
     * 载入配置文件
     *
     * @param string|null $path 指定配置文件路径
     * @return array
     */
    public static function load(string $path = null)
    {
        $path = static::getPath($path);

        if (empty(static::$attributes[$path])) {
            // 如果配置文件不存在则创建
            static::createDefaultConfigPathIfNotExists();
            if (!is_file($path)) {
                throw new \InvalidArgumentException("文件不存在: {$path}");
            }

            static::$attributes[$path] = (array)require $path;

            if (isset(static::$attributes[$path]['paths']['migrations'])) {
                static::$attributes[$path]['paths']['migrations'] = (array)static::$attributes[$path]['paths']['migrations'];
                foreach (static::$attributes[$path]['paths']['migrations'] as &$v) {
                    $v = rtrim(App::getAlias($v), '/');
                }
            }
            if (isset(static::$attributes[$path]['paths']['seeds'])) {
                static::$attributes[$path]['paths']['seeds'] = (array)static::$attributes[$path]['paths']['seeds'];
                foreach (static::$attributes[$path]['paths']['seeds'] as &$v) {
                    $v = rtrim(App::getAlias($v), '/');
                }
            }

            static::mergeDatabase(static::$attributes[$path]);
        }
        // 目录不存在则创建
        static::createPathsIfNotExists(static::$attributes[$path]);

        return static::$attributes[$path];
    }

    /**
     * 合并数据库配置
     *
     * @param array $attributes
     */
    protected static function mergeDatabase(array &$attributes)
    {
        if (empty($attributes['environments'])) {
            $attributes['environments'] = [];
        }

        if (!empty($attributes['environments']['database'])) {
            $uri = $attributes['environments']['database'];
        } else {
            $uri = env('DB_URI', config('db.master.uri'));
        }
        if (is_string($uri) && strpos($uri, ',') !== false) {
            $uri = explode(',', $uri);
        } else {
            $uri = (array)$uri;
        }

        if (empty($uri[0])) {
            throw new InvalidArgumentException('请在.env或db.php中配置数据库连接信息');
        }
        $parser = parse_url($uri[0]);
        if (empty($parser['path'])) {
            throw new InvalidArgumentException('请在.env或db.php中配置数据库名称');
        }
        parse_str($parser['query'], $q);

        $attributes['environments']['default_database'] = static::DEFAULT_ENV;
        $attributes['environments']['public'] = [
            'adapter' => 'mysql',
            'host' => array_get($parser, 'host', '127.0.0.1'),
            'name' => trim($parser['path'], '/'),
            'user' => array_get($q, 'user'),
            'pass' => array_get($q, 'password'),
            'port' => array_get($parser, 'port', 3306),
            'charset' => 'utf8',
        ];
    }

    /**
     * 目录不存在则创建
     *
     * @param array $attr
     */
    protected static function createPathsIfNotExists(array $attr)
    {
        $migrationsPaths = array_get($attr, 'paths.migrations', [alias('@root/resources/db/migrations')]);
        $seedsPaths = array_get($attr, 'paths.seeds', [alias('@root/resources/db/seeds')]);

        foreach ($migrationsPaths as &$path) {
            if (!is_dir($path)) {
                if (!filesystem()->mkdir($path)) {
                    throw new CannotWriteFileException("请确保[$path]目录拥有写权限");
                }
            }
        }

        foreach ($seedsPaths as &$path) {
            if (!is_dir($path)) {
                if (!filesystem()->mkdir($path)) {
                    throw new CannotWriteFileException("请确保[$path]目录拥有写权限");
                }
            }
        }

    }

    /**
     * 默认配置文件不存在则创建
     */
    protected static function createDefaultConfigPathIfNotExists()
    {
        if (is_file($path = static::getPath())) {
            return;
        }
        try {
            filesystem()->put($path, static::getDefaultConfigContent());
        } catch (\Exception $e) {
            $dir = dirname($path);
            throw new CannotWriteFileException("请确保[$dir]目录拥有写权限");
        }
    }


    protected static function getDefaultConfigContent()
    {
        return filesystem()->get(__DIR__.'/'.static::$stub);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getPath(string $path = null)
    {
        if ($path && !is_file($path)) {
            $path = "@root/config/migration/$path.php";
        }

        return alias($path ?: static::$defaultPath);
    }
}
