# BC Security

Helps keeping WordPress websites secure.

## Requirements

* [PHP](https://secure.php.net/) 7.2 or newer
* [WordPress](https://wordpress.org/) 5.3 or newer

## Limitations

* BC Security has not been tested on WordPress multisite installation.
* BC Security has not been tested on Windows server.

## Setup

Several features of BC Security depends on the knowledge of remote IP address, so it is important that you let the plugin know how your server is connected to the Internet. You can either set connection type via _Setup_ page or with `BC_SECURITY_CONNECTION_TYPE` constant.

You may also optionally provide Google API key if you want to check your website against the Google [Safe Browsing](https://transparencyreport.google.com/safe-browsing/overview) lists of unsafe web resources. The key must have [Google Safe Browsing](https://developers.google.com/safe-browsing/) API enabled. As with the connection type, you can configure the key either via _Setup_ page or with `BC_SECURITY_GOOGLE_API_KEY` constant.

**Note:** If you already have an installation with BC Security set up and would like to set up another installation in the same way, you can export plugin settings (including connection type) from the former installation and import them to the latter.

## Features

### Checklist

BC Security can help you find potential security issues or even signs of breach.

#### Basic checks

Basic checks cover common security practices. They do not require any information from third party sources to proceed and thus do not leak any information about your website:

1. Is backend editing of plugin and theme PHP files disabled?
1. Are directory listings disabled?
1. Is execution of PHP files from uploads directory forbidden?
1. Is display of PHP errors off by default? This check is only run in live environment (by default when `WP_ENV === 'production'` or `WP_ENV === 'staging'`, but this can be [customized via a filter](#customization)).
1. Is error log file not publicly available? This check is only run if both `WP_DEBUG` and `WP_DEBUG_LOG` constants are set to true.
1. Are there no common usernames like admin or administrator on the system?
1. Are user passwords hashed with [more secure hashing algorithm](https://roots.io/improving-wordpress-password-security/) than MD5 used by [WordPress by default](https://core.trac.wordpress.org/ticket/21022)?
1. Is PHP version still supported?

#### Advanced checks

Advanced checks require data from external sources, therefore they leak some information about your website and take more time to execute.

In the moment, list of installed plugins (but only those with _readme.txt_ file) is shared with WordPress.org and site URL is shared with Google.

##### WordPress core integrity check

WordPress core files verification is done in two phases:
1. Official md5 checksums from WordPress.org are used to determine if any of core files have been modified.
1. All files in root directory, _wp-admin_ directory (including subdirectories) and _wp-includes_ directory (including subdirectories) are checked against official checksums list in order to find out any unknown files.

The check uses the same checksums API as [`core verify-checksums`](https://developer.wordpress.org/cli/commands/core/verify-checksums/) command from [WP-CLI](https://wp-cli.org/).

##### Plugins integrity check

Plugin files verification works only for plugins installed from [Plugins Directory](https://wordpress.org/plugins/). The verification process is akin to the core files verification, although the API is slightly different (see [related Trac ticket](https://meta.trac.wordpress.org/ticket/3192) and [specification](https://docs.google.com/document/d/14-SMpaPtDGEBm8hE9ZwnA-vik5OvECDig32KqX8uFlg/edit)).

Important: any plugins under version control (Git or Subversion) are automatically omitted from the check.

##### Removed plugins check

Although plugins can be removed from [Plugins Directory](https://wordpress.org/plugins/) for several reasons (not only because they have [security vulnerability](https://www.wordfence.com/blog/2017/09/display-widgets-malware/)), use of removed plugins is discouraged. Obviously, this check also works only for plugins installed from Plugins Directory.

#### Safe Browsing check

Checks whether your website is included on any of Google's [lists of unsafe web resources](https://developers.google.com/safe-browsing/) - this is usually a solid indicator of compromise. Note that for this check to run you have to provide [properly configured API key](https://developers.google.com/safe-browsing/v4/urls-hashing) via [plugin setup](#setup).

#### Checklist monitoring

Both basic and advanced checks can be run manually from a dedicated page in backend, but can be also configured to run periodically (once a day) in the background. Basic checks are run via a single cron job, while each of advanced checks is run via a separate cron job.

### WordPress hardening

BC Security allows you to:
1. Disable pingbacks
1. Disable XML RPC methods that require authentication
1. Prevent usernames discovery via [REST API requests](https://developer.wordpress.org/rest-api/reference/users/) and [username enumeration](https://hackertarget.com/wordpress-user-enumeration/)
1. Check and/or validate user passwords using [Pwned Passwords](https://haveibeenpwned.com/Passwords) database and [API](https://haveibeenpwned.com/API/v2#PwnedPasswords)

#### Passwords check

Passwords are checked on user login. If password is present in the Pwned Passwords database, a non-dismissible warning is displayed in backend encouraging the user to change its password. By default, the warning is displayed on all pages, but this can be [customized via a filter](#customization).

#### Passwords validation

Passwords are validated on user creation, password change or password reset. If password is present in the Pwned Passwords database, the operation is aborted with an error message asking user to pick a different password.

### Login security

1. BC Security allows you to limit number of login attempts from single IP address. Implementation of this feature is heavily inspired by popular [Limit Login Attempts](https://wordpress.org/plugins/limit-login-attempts/) plugin with an extra feature of immediate blocking of specific usernames (like _admin_ or _administrator_).
1. BC Security offers an option to only display generic error message as a result of failed login attempt when wrong username, email or password is provided.

### IP blacklist

BC Security maintains a list of IP addresses with limited access to the website. This list is automatically populated by [Login Security](#login-security) module, but manual addition of IP addresses is also possible.

Out-dated records are automatically removed from the list by WP-Cron job scheduled to run every night. The job can be deactivated in backend, if desired.

### Notifications

BC Security allows to send automatic email notification to configured recipients on following occasions:

1. WordPress update is available.
1. Plugin update is available.
1. Theme update is available.
1. User with administrator privileges has logged in.
1. Known IP address has been locked out (see note below).
1. [Checklist monitoring](#checklist-monitoring) triggers an alert. Note: there is one notification sent if any of basic checks fails, but separate notification is sent if any of advanced checks fails.
1. BC Security plugin has been deactivated.

Note: _Known IP address_ is an IP address from which a successful login attempt had been previously made. Information about successful login attempts is fetched from [event logs](#events-logging).

You can mute all email notifications by setting constant `BC_SECURITY_MUTE_NOTIFICATIONS` to `true` via `define('BC_SECURITY_MUTE_NOTIFICATIONS', true);`. If you run a website in multiple environments (development, staging, production etc.), you may find it disturbing to receive email notifications from development or any environment other than production. Declaring the constant for particular environment only is very easy, if you use a [multi-environment setup](https://github.com/chesio/wp-multi-env-config).

### Events logging

BC Security logs both short and long lockout events (see [Login Security](#login-security) feature). Also, the following events triggered by WordPress core are logged:

1. Attempts to authenticate with bad cookie
1. Failed and successful login attempts
1. Requests that result in 404 page

Logs are stored in database and can be viewed on backend. Logs are automatically deleted based on their age and overall size: by default no more than 20 thousands of records are kept and any log records older than 365 days are removed, but these limits can be configured.

## Customization

Some of the modules listed above come with settings panel. Further customization can be done with filters provided by plugin:

* `bc-security/filter:is-admin` - filters boolean value that determines whether current user is considered an admin user. This check determines whether admin login notification should be sent for particular user. By default, any user with `manage_options` capability is considered an admin (or `manage_network` on multisite).
* `bc-security/filter:is-live` - filters boolean value that determines whether your website is running in a live environment.
* `bc-security/filter:plugin-changelog-url` - filters changelog URL of given plugin. Might come handy in case of plugins not hosted in Plugins Directory.
* `bc-security/filter:obvious-usernames` - filters array of common usernames that are being checked via [checklist check](#basic-checks). By default, the array consists of _admin_ and _administrator_ values.
* `bc-security/filter:plugins-to-check-for-integrity` - filters array of plugins that should have their integrity checked. By default, the array consists of all installed plugins that have _readme.txt_ file. Note that plugins under version control are automatically omitted.
* `bc-security/filter:plugins-to-check-for-removal` - filters array of plugins to check for their presence in WordPress.org Plugins Directory. By default, the array consists of all installed plugins that have _readme.txt_ file.
* `bc-security/filter:modified-files-ignored-in-core-integrity-check` - filters array of files that should not be reported as __modified__ in checksum verification of core WordPress files. By default, the array consist of _wp-config-sample.php_ and _wp-includes/version.php_ values.
* `bc-security/filter:unknown-files-ignored-in-core-integrity-check` - filters array of files that should not be reported as __unknown__ in checksum verification of core WordPress files. By default, the array consist of _.htaccess_, _wp-config.php_, _liesmich.html_, _olvasdel.html_ and _procitajme.html_ values.
* `bc-security/filter:show-pwned-password-warning` - filters whether the ["pwned password" warning](#passwords-check) should be displayed for current user on current screen.
* `bc-security/filter:ip-blacklist-default-manual-lock-duration` - filters number of seconds that is used as default value in lock duration field of manual IP blacklisting form. By default, the value is equal to one month in seconds.
* `bc-security/filter:is-ip-address-locked` - filters boolean value that determines whether given IP address is currently locked within given scope. By default, the value is based on plugin bookkeeping data.
* `bc-security/filter:log-404-event` - filters boolean value that determines whether current HTTP request that resulted in [404 response](https://en.wikipedia.org/wiki/HTTP_404) should be logged or not. To completely disable logging of 404 events, you can attach [`__return_false`](https://developer.wordpress.org/reference/functions/__return_false/) function to the filter.
* `bc-security/filter:events-with-hostname-resolution` - filters array of IDs of events for which hostname of involved IP address should be resolved via reverse DNS lookup. By default the following events are registered: attempts to authenticate with bad cookie, failed and successful login attempts and lockout events. Note that this functionality only relates to event logs report in backend - in case email notification is sent, hostname of reported IP address (if any) is always resolved separately.
* `bc-security/filter:username-blacklist` - filters array of blacklisted usernames. Blacklisted usernames cannot be registered when opening new account and any login attempt using non-existing blacklisted username triggers long lockout. There are no default values, but the filter operates on usernames set via module settings, so it can be used to enforce blacklisting of particular usernames.

## Credits

1. [Login Security](#login-security) feature is inspired by [Limit Login Attempts](https://wordpress.org/plugins/limit-login-attempts/) plugin by Johan Eenfeldt.
2. [WordPress core integrity check](#wordpress-core-integrity-check) is heavily inspired by [Checksum Verifier](https://github.com/pluginkollektiv/checksum-verifier) plugin by Sergej Müller.
3. Some features (like "[Removed plugins check](#removed-plugins-check)" or "[Usernames discovery prevention](#wordpress-hardening)") are inspired by [Wordfence Security](https://wordpress.org/plugins/wordfence/) from [Defiant](https://www.defiant.com/).
4. [Passwords check](#passwords-check) and [passwords validation](#passwords-validation) features uses API and data made available by [Have I Been Pwned](https://haveibeenpwned.com) project by [Troy Hunt](https://www.troyhunt.com).
5. Big thanks to [Vincent Driessen](https://nvie.com/about/) for his "[A successful Git branching model](https://nvie.com/posts/a-successful-git-branching-model/)" article that I find particularly useful every time I do some work on BC Security.
6. Big thanks to [Viktor Szépe](https://github.com/szepeviktor) for introducing me to [PHPStan](https://github.com/phpstan/phpstan).
7. Part of [psr/log](https://packagist.org/packages/psr/log) package codebase is shipped with the plugin.

## Alternatives (and why I do not use them)

1. [Wordfence Security](https://wordpress.org/plugins/wordfence/) - likely the current number one plugin for WordPress Security. My problem with Wordfence is that _"when you use [Wordfence], statistics about your website visitors are automatically collected"_ (see the full [Terms of Use and Privacy Policy](https://www.wordfence.com/terms-of-use-and-privacy-policy/)). In other words, in order to offer some of its great features, Wordfence is [phoning home](https://en.wikipedia.org/wiki/Phoning_home).
1. [All In One WP Security & Firewall](https://wordpress.org/plugins/all-in-one-wp-security-and-firewall/) - another very popular security plugin for WordPress. I have used AIOWPSF for quite some time; it has a lot of features, but also lot of small bugs (sometimes [not that small](https://sumofpwn.nl/advisory/2016/cross_site_scripting_in_all_in_one_wp_security___firewall_wordpress_plugin.html)). I [used to contribute](https://github.com/Arsenal21/all-in-one-wordpress-security/commits?author=chesio) to the plugin, but the codebase is [rather messy](https://github.com/Arsenal21/all-in-one-wordpress-security/pull/34) and after some time I got tired struggling with it.
