<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit72075da53358459b722b11d9f9a5bc6b
{
    public static $classMap = array (
        'WPAZ_Plugin_Base\\V_2_0\\Abstract_Plugin' => __DIR__ . '/..' . '/wordpress-phoenix/abstract-plugin-base/src/abstract_plugin.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit72075da53358459b722b11d9f9a5bc6b::$classMap;

        }, null, ClassLoader::class);
    }
}
