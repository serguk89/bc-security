<?php

namespace BlueChip\Security\Modules\IpBlacklist;

/**
 * IP access is restricted based on scope.
 */
interface LockScope
{
    /**
     * Not a real scope, just a safe value to use whenever scope is undefined.
     */
    public const ANY = 0;

    /**
     * No access to admin (or login attempt) from IP address is allowed.
     */
    public const ADMIN = 1;

    /**
     * No comments from IP address are allowed.
     */
    public const COMMENTS = 2;

    /**
     * No access to website from IP address is allowed.
     */
    public const WEBSITE = 3;
}
