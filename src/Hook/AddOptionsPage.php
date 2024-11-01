<?php

namespace XPressLite\Hook;

class AddOptionsPage
{
    public static function register()
    {
        add_action('admin_menu', [self::class, 'registerOptionsPage']);
    }

    public static function registerOptionsPage()
    {
        add_options_page(__('XPress Forum User Bridge Settings', 'xpress-lite'), __('XPress Forum User Bridge', 'xpress-lite'), 'manage_options', 'xpress-lite',
            [self::class, 'renderOptionPage']);
    }

    public static function renderOptionPage()
    {
        printf('<h1>%s</h1>', __('XPress Forum User Bridge Settings', 'xpress-lite'));
        
        echo '<form method="post" action="options.php">';

        settings_fields('xpress-lite');

        do_settings_sections('xpress-lite');

        echo submit_button();

        echo '</form>';
    }
}