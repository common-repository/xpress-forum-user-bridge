<?php
/*
* Plugin Name: XPress Forum User Bridge
* Version: 1.0.0
* Author: ThemeHouse
* Author URI: https://www.themehouse.com/
* Text Domain: thxpresslite
* Description: An integration to bring the best blogging/content management system to XenForo and the best forum software to WordPress.
*/

define('THXPRESS_LITE_PLUGIN_DIR', dirname(__FILE__));

require_once THXPRESS_LITE_PLUGIN_DIR . '/src/XPressLite.php';
/** @noinspection PhpUnhandledExceptionInspection */
\XPressLite::start(THXPRESS_LITE_PLUGIN_DIR);