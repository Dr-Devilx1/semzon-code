<?php
require __DIR__ . '/wp-stub.php';
$BUILD = __DIR__ . '/build';
$GLOBALS['theme_dir'] = $BUILD . '/semzon-child';
$GLOBALS['did'] = array( 'elementor/loaded' );

require $BUILD . '/semzon-child/functions.php';
do_action( 'after_setup_theme' );
do_action( 'init' );

echo "--- rendering header.php ---\n";
ob_start();
include $BUILD . '/semzon-child/header.php';
$h = ob_get_clean();
echo "header rendered OK, " . strlen($h) . " bytes\n";

echo "--- rendering footer.php ---\n";
ob_start();
include $BUILD . '/semzon-child/footer.php';
$f = ob_get_clean();
echo "footer rendered OK, " . strlen($f) . " bytes\n";

echo "--- action bar ---\n";
ob_start(); do_action('wp_footer'); $x = ob_get_clean();
echo "wp_footer OK, " . strlen($x) . " bytes\n";
