<?php

namespace Swoft\Blade;

//use Swoft\Blade\Compilers\BladeCompiler;
//use Swoft\Blade\Compilers\CompilerInterface;
//use Swoft\Blade\Contracts\View;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class Blade
 */
class Blade
{
     /**
      * @return View
      */
     public static function view()
     {
         return bean('blade_view');
     }

     /**
      * @return CompilerInterface
      */
     public static function compiler()
     {
         return bean('blade_view')->getEngineResolver()->resolve('blade')->getCompiler();
     }

     public function __call($name, $arguments)
     {
         $compiler = bean('blade_view')->getEngineResolver()->resolve('blade')->getCompiler();

         return $compiler->$name(...$arguments);
     }
}
