<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'wordpress');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '`X8Y~1m21dK|+`:-i8k+dn{8Y=0RlY|>2gItE:~:(4;9g$:L*[+@+WZRK%g-*|Rm');
define('SECURE_AUTH_KEY',  'bc_r^FZD$yIH%:EP,5oHb873#$P&c6PtdVf2u `x33T> 5=^BuW}++]Mh|9kV?~z');
define('LOGGED_IN_KEY',    'hA|iEzSf|~eZy9]BtC75q26v6Z5Pdm,Up>?ZEhc@urAjaY<-:K@?Qk@!*/DV&_m5');
define('NONCE_KEY',        'tbLSRW[!l CwtTR> BXU>)]FPLIT- FB&QX)vU}  cY(0-S5jx6H+#If?j g^eN6');
define('AUTH_SALT',        '49xRXi(}_0+}n/?o5Ip{[ej[(zbDB.#O+4YN[D1Q@WXAl4Nt^ToLsho#+>y+0<F}');
define('SECURE_AUTH_SALT', '!$n`s{G1Tw[vx4O13*x?]2YG_=N$#$98Mi/]|- |~X!+T,!^0I2<r0`l>FUfZR+2');
define('LOGGED_IN_SALT',   '95!EByEC #A/]RM[X26iKfAi?:n2{bpyf(i^UD@kY?&-fi=W-#lUFkW?j6R&G}* ');
define('NONCE_SALT',       'gnloL(uyxjM80tmqtgZhlO0ItT%4agq*oo(Oo7KT-UH~<wn@7w(_0]$/?e3U3P`u');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
define('WP_SIFTSCI_DEV', 'http://wcss.test:8085/app.js');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
