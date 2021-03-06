<?php

namespace BlueChip\Security\Modules\Checklist\Checks;

use BlueChip\Security\Modules\Checklist;

class NoMd5HashedPasswords extends Checklist\BasicCheck
{
    /**
     * @var string Prefix of default, MD5-based hashes
     */
    private const WP_OLD_HASH_PREFIX = '$P$';


    /**
     * @var \wpdb
     */
    private $wpdb;


    /**
     * @param \wpdb $wpdb
     */
    public function __construct(\wpdb $wpdb)
    {
        parent::__construct(
            __('No default MD5 password hashes', 'bc-security'),
            \sprintf(
                /* translators: 1: link to plugin with alternative implementation of password hashing scheme */
                esc_html__('WordPress by default uses an MD5 based password hashing scheme that is too cheap and fast to generate cryptographically secure hashes. For modern PHP versions, there are %1$s available.', 'bc-security'),
                '<a href="https://github.com/roots/wp-password-bcrypt" rel="noreferrer">' . esc_html__('more secure alternatives', 'bc-security') . '</a>'
            )
        );

        $this->wpdb = $wpdb;
    }


    protected function runInternal(): Checklist\CheckResult
    {
        // Get all users with old hash prefix
        $result = $this->wpdb->get_results(\sprintf(
            "SELECT `user_login` FROM {$this->wpdb->users} WHERE `user_pass` LIKE '%s%%';",
            self::WP_OLD_HASH_PREFIX
        ));

        if ($result === null) {
            return new Checklist\CheckResult(null, esc_html__('BC Security has failed to determine whether there are any users with password hashed with default MD5-based algorithm.', 'bc-security'));
        } else {
            return empty($result)
                ? new Checklist\CheckResult(true, esc_html__('No users have password hashed with default MD5-based algorithm.', 'bc-security'))
                : new Checklist\CheckResult(false, esc_html__('The following users have their password hashed with default MD5-based algorithm:', 'bc-security') . ' <em>' . \implode(', ', wp_list_pluck($result, 'user_login')) . '</em>')
            ;
        }
    }
}
