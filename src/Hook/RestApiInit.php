<?php

namespace XPressLite\Hook;

class RestApiInit
{
    public static function register()
    {
        add_action('rest_api_init', [self::class, 'initEndpoint']);
    }

    public static function initEndpoint()
    {
        register_rest_route('/xpress-lite/v1', '/roles', [
            'methods' => 'GET',
            'callback' => [self::class, 'getRoles'],
            'permission_callback' => [self::class, 'permissionCallback']
        ]);

        register_rest_route('/xpress-lite/v1', '/user', [
            'methods' => 'GET',
            'callback' => [self::class, 'getUser'],
            'permission_callback' => [self::class, 'permissionCallback']
        ]);
    }

    public static function permissionCallback()
    {
        $headers = getallheaders();
        if (isset($headers['X-XPressLiteApiKey'])) {
            return $headers['X-XPressLiteApiKey'] === get_option('xpress_lite_wp_api_key');
        }
        return false;
    }

    public static function getUser(\WP_REST_Request $request)
    {
        $email = $request->get_param('email');
        $user = get_user_by('email', $email);
        return $user ? $user->roles : null;
    }

    public static function getRoles()
    {
        global $wp_roles;

        $roles = [];
        foreach ($wp_roles->roles as $role => $roleData) {
            $roles[$role] = $roleData['name'];
        }

        return $roles;
    }
}