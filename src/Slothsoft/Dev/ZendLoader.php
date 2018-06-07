<?php
declare(strict_types = 1);
namespace Slothsoft\Dev;

/*
use Zend\Stdlib\ArrayUtils;
use Zend\Mvc\Application;

class ZendLoader
{

    protected static $zendPath = './zend/';

    protected static $defaultConfig = [
        'modules' => [
            'Zend\Router'
        ],
        'module_listener_options' => [
            // This should be an array of paths in which modules reside.
            // If a string key is provided, the listener will consider that a module
            // namespace, the value of that key the specific path to that module's
            // Module class.
            'module_paths' => [
                __DIR__ . '/../zend/module/',
                __DIR__ . '/../zend/vendor/'
            ],
            
            // An array of paths from which to glob configuration files after
            // modules are loaded. These effectively override configuration
            // provided by modules themselves. Paths may use GLOB_BRACE notation.
            'config_glob_paths' => [                // __DIR__ . '/autoload/{{,*.}global,{,*.}local}.php',
            ],
            
            // Whether or not to enable a configuration cache.
            // If enabled, the merged configuration will be cached and used in
            // subsequent requests.
            'config_cache_enabled' => false,
            
            // The key used to create the configuration cache file name.
            'config_cache_key' => 'application.config.cache',
            
            // Whether or not to enable a module class map cache.
            // If enabled, creates a module class map cache which will be used
            // by in future requests, to reduce the autoloading process.
            'module_map_cache_enabled' => false,
            
            // The key used to create the class map cache file name.
            'module_map_cache_key' => 'application.module.cache',
            
            // The path in which to cache merged configuration.
            'cache_dir' => './zend/data/cache/'
            
            // Whether or not to enable modules dependency checking.
            // Enabled by default, prevents usage of modules that depend on other modules
            // that weren't loaded.
            // 'check_dependencies' => true,
        ]
    ];

    public static function loadZend()
    {
        $zendFile = self::$zendPath . 'vendor/autoload.php';
        return file_exists($zendFile) ? include_once $zendFile : false;
    }

    public static function getApplication($name, $config = 'application')
    {
        $ret = null;
        if (self::loadZend()) {
            $configFile = sprintf('%sapplication/%s/%s.config.php', self::$zendPath, $name, $config);
            
            if (file_exists($configFile)) {
                $appConfig = ArrayUtils::merge(include $configFile, self::$defaultConfig);
                // my_dump($appConfig['module_listener_options']['module_paths'][0]);
                // my_dump(file_exists($appConfig['module_listener_options']['module_paths'][0]));
                // my_dump(file_exists($appConfig['module_listener_options']['module_paths'][0] . $name));
                
                $ret = Application::init($appConfig);
            }
        }
        return $ret;
    }
}

//*/