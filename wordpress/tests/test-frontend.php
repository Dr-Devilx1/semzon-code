<?php
/** Faithful-ish front-end request with ACF + Elementor present. */
require __DIR__ . '/wp-stub.php';
$BUILD = __DIR__ . '/build';
$GLOBALS['theme_dir'] = $BUILD . '/semzon-child';
$GLOBALS['did'] = array( 'elementor/loaded' );
define( 'ELEMENTOR_PRO_VERSION', '3.21' );

// --- ACF present, with a populated plant-flow options page -----------------
class ACF {}
$GLOBALS['rowstack'] = array();
$GLOBALS['acf_option_data'] = array(
    'plant_flow_stages' => array(
        array( 'stage_number' => '01', 'stage_title' => 'Intake', 'stage_description' => 'd',
               'machines' => array( array( 'machine_name'=>'Pre-cleaner','machine_spec'=>'CP','linked_product'=>101 ) ) ),
        array( 'stage_number' => '02', 'stage_title' => 'Grinding', 'stage_description' => 'd',
               'machines' => array( array( 'machine_name'=>'Hammer mill','machine_spec'=>'SMSP','linked_product'=>102 ) ) ),
    ),
);
// minimal but faithful have_rows/the_row/get_sub_field
function have_rows( $field, $post_id = false ) {
    $key = $field . '|' . ( $post_id ?: 'post' );
    if ( ! isset( $GLOBALS['rowstack'][ $key ] ) ) {
        $rows = null;
        if ( 'option' === $post_id && isset( $GLOBALS['acf_option_data'][ $field ] ) ) {
            $rows = $GLOBALS['acf_option_data'][ $field ];
        } elseif ( ! empty( $GLOBALS['currentrow'][ $field ] ) ) {
            $rows = $GLOBALS['currentrow'][ $field ];
        }
        if ( ! $rows ) { return false; }
        $GLOBALS['rowstack'][ $key ] = array( 'rows' => $rows, 'i' => -1 );
    }
    $s =& $GLOBALS['rowstack'][ $key ];
    if ( $s['i'] + 1 < count( $s['rows'] ) ) { return true; }
    unset( $GLOBALS['rowstack'][ $key ] );
    return false;
}
function the_row() {
    foreach ( $GLOBALS['rowstack'] as $k => &$s ) {
        if ( $s['i'] + 1 < count( $s['rows'] ) ) {
            $s['i']++;
            $GLOBALS['currentrow'] = $s['rows'][ $s['i'] ];
            return $GLOBALS['currentrow'];
        }
    }
    return array();
}
function get_sub_field( $n ) { return $GLOBALS['currentrow'][ $n ] ?? ''; }

// --- Elementor present -----------------------------------------------------
eval('namespace Elementor; class Plugin { public static $instance; public $kits_manager; public $files_manager; }');
\Elementor\Plugin::$instance = new \Elementor\Plugin();

$GLOBALS['posts'][101] = array( 'post_type'=>'product','post_name'=>'maize-pre-cleaner','post_title'=>'Maize Pre-Cleaner' );
$GLOBALS['posts'][102] = array( 'post_type'=>'product','post_name'=>'hammer-mill','post_title'=>'Hammer Mill' );
$GLOBALS['options']['semzon_google_maps_key'] = 'TESTKEY';
$GLOBALS['options']['semzon_map_locations'] = array( array('name'=>'HQ','lat'=>31.2,'lng'=>74.1,'cap'=>'X','type'=>'hq') );

require $BUILD . '/semzon-child/functions.php';
do_action( 'after_setup_theme' );
do_action( 'init' );
do_action( 'acf/init' );

echo "--- wp_enqueue_scripts (front end) ---\n";
do_action( 'wp_enqueue_scripts' );
$d = $GLOBALS['localized']['SEMZON_DATA'] ?? null;
echo "SEMZON_DATA flow stages: " . count( $d['flow'] ?? [] ) . "\n";
echo "  first stage: " . json_encode( $d['flow'][0] ?? null ) . "\n";
echo "  mapsKey: " . ( $d['mapsKey'] ?: '(empty)' ) . "\n";

echo "--- template_redirect ---\n";
do_action( 'template_redirect' );
echo "ok\n";

echo "--- header.php ---\n";
ob_start(); include $BUILD . '/semzon-child/header.php'; $h = ob_get_clean();
echo "ok, " . strlen($h) . " bytes; mega panels: " . substr_count($h,'class="mega"') . "\n";

echo "--- footer.php ---\n";
ob_start(); include $BUILD . '/semzon-child/footer.php'; $f = ob_get_clean();
echo "ok, " . strlen($f) . " bytes\n";
echo "\nNO FATAL on a full front-end request.\n";
