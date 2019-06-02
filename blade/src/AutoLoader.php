<?php declare(strict_types=1);

namespace Swoft\Blade;

use Swoft\Bean\BeanFactory;

use Swoft\Helper\ComposerJSON;
use Swoft\SwoftComponent;
use Swoft\Blade\Compilers\BladeCompiler;

use Swoft\Blade\Engines\CompilerEngine;
use Swoft\Blade\Engines\EngineResolver;
use Swoft\Blade\Engines\FileEngine;
use Swoft\Blade\Engines\PhpEngine;

/**
 * Class AutoLoader
 *
 * @since 2.0
 */
class AutoLoader extends SwoftComponent
{
    protected $container;

    /**
     * Get namespace and dir
     *
     * @return array
     * [
     *     namespace => dir path
     * ]
     */
    public function getPrefixDirs(): array
    {
        return [__NAMESPACE__ => __DIR__];
    }

    /**
     * Metadata information for the component.
     *
     * @return array
     * @see ComponentInterface::getMetadata()
     */
    public function metadata(): array
    {

        return [];
//        $jsonFile = \dirname(__DIR__) . '/../composer.json';
        // var_dump(expression)($jsonFile);
//        return ComposerJSON::open($jsonFile)->getMetadata();
    }

    /**
     * @return array
     */
    public function beans(): array
    {
//        var_dump(\bean('view'));
//        var_dump("dsfsdf");
//        $this->container = BeanFactory::getContainer();
//        $this->register();
//        return [];
        return [
            'blade' => [
                'class'     => Blade::class
            ],
            'view_finder' => [
                'class' => FileViewFinder::class,
            ],
            'blade_compiler' => [
                'class' => BladeCompiler::class,
            ],
            'FileEngine' => [
                'class' => FileEngine::class,
            ],
            'PhpEngine' => [
                'class' => PhpEngine::class,
            ],
//            'CompilerEngine' => [
//                'class' => CompilerEngine::class,
//            ],
            'view_engine_resolver' => [
                'class' => EngineResolver::class,
                'resolvers' => [
                    'file' => function () {return new FileEngine();},
//                    'blade' => \bean("FileEngine"),
                    'php' => function () {return new PhpEngine();},
                    'blade' => function () {return new CompilerEngine(\bean("blade_compiler"));},
                ]
            ],
            'blade_view' => [
                'class' => Factory::class,
//                'engines' => \bean('redis')\bean(ConnectListener::class),bean(HandshakeListener::class),\bean(XmlRequestParser::class),'${bean}'EngineResolver::class,\bean(ConnectListener::class)'${connectListener}',
//                'engines' => '${\bean("view.engine.resolver")}',
                'engines' => \bean('view_engine_resolver'),
                'finder' => \bean('view_finder'),
                'path'     => '@base/resources/views',
                'compiled' => '@base/runtime/views',
                'namespaces' => [],
                // 静态资源读取目录
                'assets' => [],
                // 开启加载静态资源
                'read-assets' => true,
            ],

        ];
    }


















    


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerViewFinder();

        $this->registerEngineResolver();

        $this->registerFactory();
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory()
    {
        $this->container->create("blade_view",[
            'blade_view' => [
                'class' => Factory::class,
                'name'  => 'blade_view',
                'path' => '@base/resources/views', // 默认模板路径
                'compiled' => '@base/runtime/views', // 编译模板缓存路径
                'namespaces' => [ // 视图命名空间
                ],
                'assets' => [ // 静态资源读取目录
                ],
                'read-assets' => true, // 开启加载静态资源
            ]
        ]);

        /* @var Factory $factory */
        $factory = $this->container->get('blade_view');
        $factory->setEngineResolver(
            $this->container->get('view.engine.resolver')
        );
        $factory->setViewFinder(
            $this->container->get('view.finder')
        );
    }

}
