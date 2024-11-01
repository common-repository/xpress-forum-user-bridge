<?php

if (!class_exists('\XPressLite')) {
    /** @noinspection PhpUndefinedClassInspection */
    class XPressLite
    {
        protected static $rootDirectory;

        protected static $autoLoader;

        protected static $hooks = ['RegisterSettings', 'AddOptionsPage', 'Init', 'RestApiInit', 'AfterPluginRow'];
        protected static $filters = ['Authenticate'];

        public static function start($rootDirectory)
        {
            self::$rootDirectory = $rootDirectory;
            self::startAutoloader();

            self::registerHooks();
            self::registerFilters();
        }

        protected static function startAutoloader()
        {
            if (self::$autoLoader) {
                return;
            }

            /** @noinspection PhpUndefinedClassInspection */
            /** @var \Composer\Autoload\ClassLoader $autoLoader */
            /** @noinspection PhpIncludeInspection */
            $autoLoader = require(self::$rootDirectory . '/vendor/autoload.php');
            $autoLoader->register();

            self::$autoLoader = $autoLoader;
        }

        protected static function registerHooks()
        {
            foreach (self::$hooks as $hook) {
                $class = '\\XPressLite\\Hook\\' . $hook;
                (new $class())::register();
            }
        }
        
        protected static function registerFilters()
        {
            foreach (self::$filters as $filter) {
                $class = '\\XPressLite\\Filter\\' . $filter;
                (new $class())::register();
            }
        }
    }
}