<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

use Object\ACL\Resources;

class User
{
    // roles/teams/groups constants
    public const ROLES = 'roles';
    public const ROLE_IDS = 'role_ids';
    public const ROLE_NAMES = 'role_names';
    public const TEAMS = 'team_codes';
    public const TEAM_IDS = 'teams';
    public const TEAM_NAMES = 'team_names';
    public const GROUPS = 'group_codes';
    public const GROUP_IDS = 'group_ids';
    public const GROUP_NAMES = 'group_names';

    /**
     * Cached users
     *
     * @var array
     */
    public static $cached_users = [];

    /**
     * Cached owners
     *
     * @var array
     */
    public static $cached_owners;

    /**
     * Override user id
     *
     * @var int
     */
    protected static $override_user_id;

    /**
     * Set user
     *
     * @param int|null $user_id
     */
    public static function setUser($user_id)
    {
        self::$override_user_id = $user_id;
    }

    /**
     * Get user
     *
     * @return int|null
     */
    public static function getUser()
    {
        return self::$override_user_id;
    }

    /**
     * User #
     *
     * @return int
     */
    public static function id()
    {
        return $_SESSION['numbers']['user']['id'] ?? null;
    }

    /**
     * Authorized
     *
     * @return boolean
     */
    public static function authorized(): bool
    {
        return (!empty($_SESSION['numbers']['flag_authorized']) ? true : false);
    }

    /**
     * Get
     *
     * @param mixed $key
     * @return mixed
     */
    public static function get($key)
    {
        if (!empty(self::$override_user_id)) {
            return array_key_get(self::$cached_users[self::$override_user_id], $key);
        } elseif (isset($_SESSION['numbers']['user'])) {
            return array_key_get($_SESSION['numbers']['user'], $key);
        }
    }

    /**
     * Set
     *
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        if (!empty(self::$override_user_id)) {
            array_key_set(self::$cached_users[self::$override_user_id], $key, $value);
        } elseif (isset($_SESSION['numbers']['user'])) {
            array_key_set($_SESSION['numbers']['user'], $key, $value);
        }
        return $value;
    }

    /**
     * Authorize user
     *
     * @param array $data
     */
    public static function userAuthorize(array $data)
    {
        $_SESSION['numbers']['user'] = $data;
        // flag as authorized
        $_SESSION['numbers']['flag_authorized'] = true;
        // add authorized role
        $roles = Resources::getStatic('user_roles', 'authorized', 'data');
        self::roleGrant($roles);
    }

    /**
     * Sign out user
     *
     * @param array $data
     */
    public static function userSignOut()
    {
        $_SESSION['numbers']['user'] = [];
        $_SESSION['numbers']['flag_authorized'] = false;
    }

    /**
     * Roles
     *
     * @param string $type
     *      ROLES
     *      ROLE_IDS
     *      ROLE_NAMES
     * @return array
     */
    public static function roles(string $type = self::ROLES): array
    {
        if (!empty(self::$override_user_id)) {
            return self::$cached_users[self::$override_user_id][$type] ?? [];
        } else {
            return $_SESSION['numbers']['user'][$type] ?? [];
        }
    }

    /**
     * Teams
     *
     * @param string $type
     *      TEAMS
     *      TEAM_IDS
     *      TEAM_NAMES
     * @return array
     */
    public static function teams(string $type = self::TEAM_IDS): array
    {
        if (!empty(self::$override_user_id)) {
            return self::$cached_users[self::$override_user_id][$type] ?? [];
        } else {
            return $_SESSION['numbers']['user'][$type] ?? [];
        }
    }

    /**
     * Groups
     *
     * @param string $type
     *      GROUPS
     *      GROUP_IDS
     *      GROUP_NAMES
     * @return array
     */
    public static function groups(string $type = self::GROUP_IDS): array
    {
        if (!empty(self::$override_user_id)) {
            return self::$cached_users[self::$override_user_id][$type] ?? [];
        } else {
            return $_SESSION['numbers']['user'][$type] ?? [];
        }
    }

    /**
     * Grant role(s)
     *
     * @param string|array $role
     */
    public static function roleGrant($role)
    {
        // add roles
        if (!empty($role)) {
            // initialize roles array
            if (!isset($_SESSION['numbers']['user']['roles'])) {
                $_SESSION['numbers']['user']['roles'] = [];
            }
            if (!is_array($role)) {
                $role = [$role];
            }
            $_SESSION['numbers']['user']['roles'] = array_unique(array_merge($_SESSION['numbers']['user']['roles'], $role));
        }
    }

    /**
     * Revoke role(s)
     *
     * @param string|array $role
     */
    public static function roleRevoke($role)
    {
        if (!empty($role) && !empty($_SESSION['numbers']['user']['roles'])) {
            if (!is_array($role)) {
                $role = [$role];
            }
            foreach ($role as $v) {
                $key = array_search($v, $_SESSION['numbers']['user']['roles']);
                if ($key !== false) {
                    unset($_SESSION['numbers']['user']['roles'][$key]);
                }
            }
        }
    }

    /**
     * Check if role(s) exists
     *
     * @param string|int|array $role
     * @param string $type
     *      ROLES
     *      ROLE_IDS
     *      ROLE_NAMES
     * @return boolean
     */
    public static function roleExists(string|int|array $role, string $type = self::ROLES): bool
    {
        if (empty($_SESSION['numbers']['user'][$type])) {
            return false;
        }
        if (is_array($role)) {
            $temp = array_intersect($role, self::get($type));
            return !empty($temp);
        } else {
            return in_array($role, self::get($type));
        }
    }

    /**
     * Check if team(s) exists
     *
     * @param string|int|array $team
     * @param string $type
     *      TEAMS
     *      TEAM_IDS
     *      TEAM_NAMES
     * @return boolean
     */
    public static function teamExists(string|int|array $team, string $type = self::TEAM_IDS): bool
    {
        return self::roleExists($team, $type);
    }

    /**
     * Check if group(s) exists
     *
     * @param string|int|array $group
     * @param string $type
     *      GROUPS
     *      GROUP_IDS
     *      GROUP_NAMES
     * @return boolean
     */
    public static function groupExists(string|int|array $group, string $type = self::GROUPS): bool
    {
        return self::roleExists($group, $type);
    }
}
