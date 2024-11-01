<?php

namespace XPressLite\Hook;

class AfterPluginRow
{
    public static function register()
    {
        add_action('plugin_action_links', [self::class, 'addLinks'], 10, 4);
    }

    public static function addLinks($actions, $plugin_file, $plugin_data, $context)
    {
        if ($plugin_data['Name'] == 'XPress Forum User Bridge') {
            $actions['download_xf_plugin'] = '<a href="https://www.themehouse.com/xenforo/2/addons/xpress-forum-user-bridge" target="_blank">' . __('Download XenForo add-on') . '</a>';

            $actions['buy_xpress'] = '<a href="https://www.themehouse.com/xenforo/2/addons/xpress" target="_blank"><strong style="color: #4CAF50">' . __('Buy XPress') . '</strong></a>';
        }

        return $actions;
    }
}