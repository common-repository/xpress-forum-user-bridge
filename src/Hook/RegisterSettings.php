<?php

namespace XPressLite\Hook;

use GuzzleHttp\Client;

class RegisterSettings
{
    public static function register()
    {
        add_action('admin_init', [self::class, 'registerSettings']);
    }

    public static function registerSettings()
    {
        register_setting('xpress-lite', 'xpress_lite_xf_url', [
            'type' => 'string',
            'description' => 'XenForo URL'
        ]);

        register_setting('xpress-lite', 'xpress_lite_xf_api_key', [
            'type' => 'string',
            'description' => 'XenForo Super User API Key'
        ]);

        register_setting('xpress-lite', 'xpress_lite_auth_with_xf', [
            'type' => 'boolean',
            'description' => 'Authenticate with XenForo switch',
            'default' => true
        ]);

        register_setting('xpress-lite', 'xpress_lite_regen_api_key', [
            'type' => 'boolean',
            'description' => 'API Key regenerate checkbox',
            'default' => true,
            'sanitize_callback' => [self::class, 'resetWPAPIKey']
        ]);

        register_setting('xpress-lite', 'xpress_lite_import_roles', [
            'type' => 'boolean',
            'description' => 'Toggle for role import from XenForo',
            'default' => true
        ]);

        register_setting('xpress-lite', 'xpress_lite_role_sync', [
            'type' => 'boolean',
            'description' => 'Role import rules',
            'default' => true
        ]);

        add_settings_section(
            'xpress-lite-xf',
            'XenForo API',
            null,
            'xpress-lite'
        );

        add_settings_field(
            'xpress_lite_xf_url',
            __('XenForo Board URL:', 'xpress-lite'),
            [self::class, 'renderXFURLInput'],
            'xpress-lite',
            'xpress-lite-xf'
        );

        add_settings_field(
            'xpress_lite_xf_api_key',
            __('XenForo API Key:', 'xpress-lite'),
            [self::class, 'renderXFAPIKeyInput'],
            'xpress-lite',
            'xpress-lite-xf'
        );

        add_settings_field(
            'xpress_lite_auth_with_xf',
            __('Authenticate with XenForo:', 'xpress-lite'),
            [self::class, 'renderAuthWithXFCheckbox'],
            'xpress-lite',
            'xpress-lite-xf'
        );

        add_settings_field(
            'xpress_lite_sync_roles',
            __('Import roles:', 'xpress-lite'),
            [self::class, 'renderImportRoles'],
            'xpress-lite',
            'xpress-lite-xf'
        );

        add_settings_section(
            'xpress-lite-wp',
            'WordPress API',
            null,
            'xpress-lite'
        );

        add_settings_field(
            'xpress_lite_wp_api_key',
            __('WordPress API Key:', 'xpress-lite'),
            [self::class, 'renderWPAPIKey'],
            'xpress-lite',
            'xpress-lite-wp'
        );
    }

    public static function renderImportRoles()
    {
        $url = get_option('xpress_lite_xf_url', '');
        $key = get_option('xpress_lite_xf_api_key', '');

        $rules = get_option('xpress_lite_role_sync', []);

        if ($url && $key) {
            try {
                $client = new Client();
                $response = $client->request('GET', $url . '/api/thxpress-lite/user-groups', [
                    'headers' => [
                        'XF-Api-Key' => $key
                    ]
                ]);
                $groups = json_decode($response->getBody()->getContents(), true)['groups'];
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                echo __('Set up XenForo API details first.', 'xpress-lite');
                return;
            }

            $value = get_option('xpress_lite_import_roles', '');

            printf(
                '<input type="checkbox" id="xpress_lite_import_roles" name="xpress_lite_import_roles" %s /><label for="xpress_lite_import_roles">%s</label>',
                $value ? 'checked' : '', __('Enabled', 'xpress-lite')
            );

            global $wp_roles;

            foreach ($groups as $group) {
                if ($group['user_group_id'] == 1) {
                    continue;
                }

                echo '<p>' .
                    sprintf(__('Make <strong>%s</strong> a ', 'xpress-lite'),
                        ($group['user_title'] ? $group['user_title'] : $group['title'])) .
                    ' <select name="xpress_lite_role_sync[' . $group['user_group_id'] . '][role]">';
                foreach ($wp_roles->roles as $roleId => $role) {
                    echo '<option value="' . $roleId . '" ' . ($roleId == (isset($rules[$group['user_group_id']]) ? $rules[$group['user_group_id']]['role'] : 'subscriber') ? ' selected ' : '') . '>' . $role['name'] . '</option>';
                }
                echo '</select> ' . __(' at priority ',
                        'xpress-lite') . '<input type="number" name="xpress_lite_role_sync[' . $group['user_group_id'] . '][priority]" value="' . (isset($rules[$group['user_group_id']]) ? $rules[$group['user_group_id']]['priority'] : 100) . '" /> .</p>';
            }

            printf('<p class="description" id="xpress_lite_xf_api_key-description">%s</p>',
                __('If enabled, the selected WordPress role will be assigned to each of your users if their XenForo account has the listed user group when they log in. Only the highest priority role that matches will be assigned.',
                    'xpress-lite')
            );
        }
    }

    public static function renderXFURLInput()
    {
        $value = get_option('xpress_lite_xf_url', '');

        printf(
            '<input type="url" id="xpress_lite_xf_url" name="xpress_lite_xf_url" value="%s" />',
            esc_attr($value)
        );

        printf('<p class="description" id="xpress_lite_xf_api_key-description">%s</p>',
            __('The URL that has been entered under XenForo Admin Control Panel > Setup > Options > Basic board information in Board URL.',
                'xpress-lite')
        );
    }

    public static function renderXFAPIKeyInput()
    {
        $value = get_option('xpress_lite_xf_api_key', '');

        printf(
            '<input type="text" id="xpress_lite_xf_api_key" name="xpress_lite_xf_api_key" value="%s" />',
            esc_attr($value)
        );

        printf('<p class="description" id="xpress_lite_xf_api_key-description">%s</p>',
            __('A XenForo Super User API Key. Can be obtained under XenForo Admin Control Panel > Setup > Service providers > API keys.',
                'xpress-lite')
        );
    }

    public static function renderAuthWithXFCheckbox()
    {
        $value = get_option('xpress_lite_auth_with_xf', '');

        printf(
            '<input type="checkbox" id="xpress_lite_auth_with_xf" name="xpress_lite_auth_with_xf" %s /><label for="xpress_lite_auth_with_xf">%s</label>',
            $value ? 'checked' : '', __('Enabled', 'xpress-lite')
        );

        printf('<p class="description" id="xpress_lite_auth_with_xf-description">%s</p>',
            __("If enabled, when no WordPress user is found for the given login credentials, XPress Forum User Bridge attempts to authenticate the user against your XenForo installation.<br/>
If a XenForo user is found, and a WordPress user for its email address exists, the WordPress user’s password will be updated, otherwise a new WordPress user will be created.",
                'xpress-lite')
        );
    }

    protected static function generateApiKey()
    {
        $newApiKey = sha1(time() . get_bloginfo('name'));
        update_option('xpress_lite_wp_api_key', $newApiKey);
        return $newApiKey;
    }

    public static function resetWPAPIKey($status)
    {
        if($status == 'on') {
            self::generateApiKey();
        }
    }

    public static function renderWPAPIKey()
    {
        $apiKey = get_option('xpress_lite_wp_api_key');

        if (!$apiKey) {
            $apiKey = self::generateApiKey();
        }

        printf('<strong>%s</strong>', $apiKey);

        printf(
            '<br/><input type="checkbox" id="xpress_lite_regen_api_key" name="xpress_lite_regen_api_key" /><label for="xpress_lite_regen_api_key">%s</label>',
            __('Regenerate', 'xpress-lite')
        );

        printf('<p class="description" id="xpress_lite_auth_with_xf-description">%s</p>',
            __("Pass this API key to your XenForo XPress Forum User Bridge options in order to connect WordPress and XenForo.
            <br/>You may regenerate it by checking the above checkbox and save this page, should the API key ever be compromised",
                'xpress-lite')
        );
    }
}