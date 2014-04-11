<?php
// ===================================================
// Load database info and local development parameters
// ===================================================
if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
	define( 'WP_LOCAL_DEV', true );
	include( dirname( __FILE__ ) . '/local-config.php' );
} else {
	define( 'WP_LOCAL_DEV', false );
	define( 'DB_NAME', 'dbwpprocoves' );
	define( 'DB_USER', 'userdbpro' );
	define( 'DB_PASSWORD', 'Procoves11b' );
	define( 'DB_HOST', 'localhost' ); // Probably 'localhost'
}

// ========================
// Custom Content Directory
// ========================
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/content' );
define( 'WP_CONTENT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/content' );

define( 'WP_PLUGIN_DIR', dirname(__FILE__) . '/content/plugins' );
define( 'WP_PLUGIN_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/content/plugins' );

define( 'WPMU_PLUGIN_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/content/mu-plugins' );

// ================================================
// You almost certainly do not want to change these
// ================================================
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// ==============================================================
// Salts, for security
// Grab these from: https://api.wordpress.org/secret-key/1.1/salt
// ==============================================================
define('AUTH_KEY',         '-+ICwkH$8qQ+Lj C74)J+[{HlI-?*Az/._vA%ScJ|B]e-|7;Y52((qaUj=>M,za6');
define('SECURE_AUTH_KEY',  'HY},&0Z92p:fQ7 ,!El-Zgod8+q7[uG;0h{4u~]ClY.tW>;4PG`oZ=%::/u6[ze&');
define('LOGGED_IN_KEY',    '-cAx:zK^s:9KporWy5Jm<;<NT]IcF?i,k<=1+,A#+|E&5`*vUWl&vm8IR23`WsL~');
define('NONCE_KEY',        'f[/>Axsk+d#xoAg1moX`;NQ#^Iz!+TIN-hWP^:ZAG,{e#x|VH+$mZ:- G~#@B&6<');
define('AUTH_SALT',        'Vg}n:{o(yERdD5M9]a-f%X34{5t(>#Ly|P V]@q($LAhA) , !p[y|&g3e,#JS+@');
define('SECURE_AUTH_SALT', 'zPGR.I#!ApcB}=>MSP~ETH]!oj92<n6 (ML!P3w7~(,Fff_R@>A|MSB0%<WEt=>4');
define('LOGGED_IN_SALT',   'Ib+3ciq+z$.XgZ4%%ht*2R--wucz`srX8)(%FScG)q6;Y`7?%@iei[$09OE}a *]');
define('NONCE_SALT',       ']G$7@wzl$qgvpT*.qiO+9i&#OG5zS[PdF,j{9WI2+9[Y6r<E1D+OZ5e+9e5^0Bx_');

// ==============================================================
// Table prefix
// Change this if you have multiple installs in the same database
// ==============================================================
$table_prefix  = 'wp_';

// ================================
// Language
// Leave blank for American English
// ================================
define( 'WPLANG', 'fr_FR' );

// ===========
// Hide errors
// ===========
ini_set( 'display_errors', 0 );
define( 'WP_DEBUG_DISPLAY', false );

// ===================
// Bootstrap WordPress
// ===================
if ( !defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/wp/' );
require_once( ABSPATH . 'wp-settings.php' );
