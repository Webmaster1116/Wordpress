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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '.6nS9bFT)]$%:qu2dm:vSu]G$)<CwEYp* FnB$Dnp{B>4UQZ%=/8sZ^.gz7d0Yhy' );
define( 'SECURE_AUTH_KEY',  'nHeEVh~QG3$xwpr?2}77ZYgM}~q^-`B=3Vw3d(I_{&*ok?3M*]D&@gCz[Uq#~jpg' );
define( 'LOGGED_IN_KEY',    'nX04bj9UEE6qyZ=wKG0#N~wrk7<)5{d{?4f _77Tl2#kK*6>Scx@LE&YL9_?+5J>' );
define( 'NONCE_KEY',        'Nl#{7-dba!AaA4L-[(:);ujByoc] dKlq$oZ?8QYS`nM5qp`v|*5DY#Q0q;%JUSI' );
define( 'AUTH_SALT',        '11M&,-0h(P_nr#`{S.D1$vtKy8KfrQ&KpRs ;-]EolCuN(68#94Kj i/v,~Y0C=o' );
define( 'SECURE_AUTH_SALT', ';#nmnV`.F_m>5F&mED$!72u~!Q-9dE5k3av+9 0WW3 ({2]]]7m,jGGWNk)8DBX(' );
define( 'LOGGED_IN_SALT',   'Yb^D9>:Fa}^8wH|vwRF|`ZWVkbfCsgh{PjNj-W013mp:At`z)AIw1qjTUmz8br}J' );
define( 'NONCE_SALT',       '>`yZfo_-;>rERK[2)>t TWWEOyClhm*}d_B09 {W/>8e.@fb:_N*xu7gq2&8RX/O' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
