<?php

namespace XPressLite\Filter;

use XFApi\Client;
use XFApi\Exception\RequestException\AbstractRequestException;
use XFApi\Exception\XFApiException;

class Authenticate
{
    /** @var Client */
    protected static $apiClient;

    public static function register()
    {
        add_filter('authenticate', [self::class, 'authenticate'], 25, 3);
        add_filter('authenticate', [self::class, 'syncRoles'], 30, 3);
    }

    public static function syncRoles($user, $login, $password)
    {
        if ($user instanceof \WP_User && $user->user_email) {
            $url = get_option('xpress_lite_xf_url', '');
            $key = get_option('xpress_lite_xf_api_key', '');

            if ($url && $key) {
                try {
                    $client = new \GuzzleHttp\Client();
                    $response = $client->request('GET', $url . '/api/thxpress-lite/user-by-email', [
                        'params' => [
                            'email' => $user->user_email
                        ],
                        'headers' => [
                            'XF-Api-Key' => $key
                        ]
                    ]);
                    $reply = json_decode($response->getBody()->getContents(), true)['user'];

                    if (!$reply) {
                        return $user;
                    }

                    $groups = $reply['secondary_group_ids'] ?: [];
                    if (is_string($groups)) {
                        $groups = explode(',', $groups);
                    }
                    $groups[] = $reply['user_group_id'];

                    $rules = get_option('xpress_lite_role_sync', []);
                    if (!is_array($rules)) {
                        $rules = [];
                    }
                    $rules = array_intersect_key($rules, array_flip($groups));

                    $match = array_shift($rules);
                    foreach ($rules as $rule) {
                        if ($rule['priority'] > $match['priority']) {
                            $match = $rule;
                        }
                    }

                    $user->set_role($match['role']);
                } catch (\Exception $e) {
                    return $user;
                }
            }
        }

        return $user;
    }

    public static function authenticate($user, $login, $password)
    {
        if (get_option('xpress_lite_auth_with_xf', false) && $login && $password) {
            if ($user instanceof \WP_User) {
                return $user;
            }

            $apiClient = self::apiClient();

            if (!$apiClient) {
                return $user;
            }

            try {
                $xfUser = $apiClient->xf->auth->auth($login, $password);
            } catch (AbstractRequestException $e) {
                return $user;
            } catch (XFApiException $e) {
                return $user;
            }

            if ($xfUser) {
                return self::createUser($xfUser, $password, $login);
            }
        }

        return $user;
    }

    protected static function createUser($xfUser, $password, $login)
    {
        if ($xfUser->email) {
            $emailUser = get_user_by('email', $xfUser->email);

            if ($emailUser) {
                wp_set_password($emailUser->ID, $password);
                return $emailUser;
            }
        }

        $nameUser = get_user_by('login', $xfUser->username);
        if ($nameUser) {
            $newName = $xfUser->email;
        } else {
            $newName = $xfUser->username;
        }

        $userId = wp_create_user($newName, $password, $xfUser->email);
        return get_user_by('ID', $userId);
    }

    public static function apiClient()
    {
        if (!self::$apiClient) {
            $url = get_option('xpress_lite_xf_url') . '/api';
            $key = get_option('xpress_lite_xf_api_key');

            if ($url && $key) {
                self::$apiClient = new Client($url, $key);
            }
        }

        return self::$apiClient;
    }
}