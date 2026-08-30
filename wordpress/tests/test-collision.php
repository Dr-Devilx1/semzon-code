<?php
/**
 * Regression: a stale copy of the OLD installer plugin (which declared
 * Semzon_CPT / Semzon_ACF itself) must not white-screen the site when the new
 * theme loads and declares them too.
 */
require __DIR__ . '/wp-stub.php';
$BUILD = __DIR__ . '/build';
$GLOBALS['theme_dir'] = $BUILD . '/semzon-child';
$GLOBALS['did'] = array( 'elementor/loaded' );

$fail = 0;
function check( $l, $c, $d = '' ) { global $fail; if ($c) { echo "  PASS  $l\n"; } else { $fail++; echo "  FAIL  $l  $d\n"; } }

echo "== a stale old plugin declares the classes FIRST ==\n";
// Plugins load before themes in WordPress, so this is the realistic order.
class Semzon_CPT   { public static function init() {} public static function register() { $GLOBALS['old_cpt_ran'] = true; } }
class Semzon_ACF   { public static function init() {} }
class Semzon_Admin { public static function init() {} }
echo "  (old plugin declared Semzon_CPT / Semzon_ACF / Semzon_Admin)\n";

echo "\n== theme loads on top — must not fatal ==\n";
require $BUILD . '/semzon-child/functions.php';
check( 'theme functions.php loaded without a duplicate-declaration fatal', true );

do_action( 'after_setup_theme' );
do_action( 'init' );
do_action( 'wp_enqueue_scripts' );
check( 'after_setup_theme / init / enqueue all ran', true );
check( 'design system still enqueued', isset( $GLOBALS['styles']['semzon-design-system'] ) );

// the activation hook must not fatal either
do_action( 'after_switch_theme' );
check( 'theme activation hook survives a foreign Semzon_CPT', true );

echo "\n== header + footer still render ==\n";
ob_start(); include $BUILD . '/semzon-child/header.php'; $h = ob_get_clean();
check( 'header.php rendered', strlen( $h ) > 500, strlen($h) . ' bytes' );
ob_start(); include $BUILD . '/semzon-child/footer.php'; $f = ob_get_clean();
check( 'footer.php rendered', strlen( $f ) > 200, strlen($f) . ' bytes' );

echo "\n== the installer plugin also survives ==\n";
require $BUILD . '/semzon-setup/semzon-setup.php';
do_action( 'plugins_loaded' );
check( 'plugin loaded alongside the stale classes', true );

echo "\n" . ( $fail ? "$fail FAILED\n" : "No fatal. Duplicate declarations are survivable.\n" );
exit( $fail ? 1 : 0 );
