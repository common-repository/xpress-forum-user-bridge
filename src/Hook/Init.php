<?php

namespace XPressLite\Hook;

class Init
{
    public static function register()
    {
        add_action('init', [self::class, 'nonce']);
    }

    public static function nonce()
    {
        $headers = getallheaders();
        if (isset($headers['X-XPressLiteApiKey'])) {
            $key = $headers['X-XPressLiteApiKey'];
            if ($key === get_option('xpress_lite_wp_api_key')) {
                $_REQUEST['_wpnonce'] = wp_create_nonce('wp_rest');

                register_rest_field('user', 'user_email',
                    array(
                        'get_callback' => function ($user) {
                            return $user['email'];
                        },
                        'update_callback' => null,
                        'schema' => null,
                    )
                );
            }
        }
    }
}