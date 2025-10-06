<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
define( 'FORCE_SSL_ADMIN', true ); // Redirect All HTTP Page Requests to HTTPS - Security > Settings > Enforce SSL
// END iThemes Security - Do not modify or remove this line

define( 'ITSEC_ENCRYPTION_KEY', 'TTVqM1YlUHlEOzdwVE48JjRBS3FfKzB2XUQlNmMoN3t2VSoyQTdAVm9YKSFfTGkpNzFkRDNZO0FmJD8mTCwjfg==' );
define( 'WP_CACHE', true ); // Added by WP Rocket

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'db_douch_flow' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'password' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'a0:t}@5Z{DGJTl%g+Z..FI+d:u,QDNS$K8w^jCfK}nVE8|`)24Rer,9^#w~dU;eF' );
define( 'SECURE_AUTH_KEY',   '9z`IbDXj95L1:~D`Gi>;>!s]7,V1]$#c|&`#V{UELX?[bqY(Gx<7G2-Pzg=]I_R0' );
define( 'LOGGED_IN_KEY',     '=A<#`8/7QPdKUDlbC[IX%%H_}(D#36r<>8}bA]`7CS){#G|,CGOpPw;Z/+kdHj/}' );
define( 'NONCE_KEY',         'Kr!+9poiV`.H,GS)7Jqj#,#5}t[3. a8dOBdlm99pp~4e;y5EZc9s(AqEoB(RnKg' );
define( 'AUTH_SALT',         '|e9;{S0f,MayDp-B/+O_6vvJ<!)VDfx&ARhNImR%Ls M*?1NR<5[a]}FEaV3{E?s' );
define( 'SECURE_AUTH_SALT',  '>nb@6O*?%C*!jRPkh#rUmS,~7sS({v0FvA<4N>gd$r9h+VaSyG[EuNu;{He]oA9}' );
define( 'LOGGED_IN_SALT',    'bM$`=ke](O9<w^aECG;>y(<7N5`xN]wV!(ume@w*!2;&3,SL?}kJPL*vBM@j.o6)' );
define( 'NONCE_SALT',        'u1TvtP1ud)uPjYafQvX,N7k_+A<wU9XA0lY}; =i|mNQ:p0|`Hix0{jp}L:j*^sh' );
define( 'WP_CACHE_KEY_SALT', 'OW[:{<T^<h}u:pPx)=} yRcMi<g5}<_W@$V+ :.+w+U).5Vg2d_Tln`0]v]tnsUI' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
