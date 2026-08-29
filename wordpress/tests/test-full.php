<?php
/**
 * Full integration harness: loads the theme, then the installer plugin, and
 * asserts the whole contract — including that the site survives deletion of
 * the plugin.
 */

require __DIR__ . '/wp-stub.php';

$BUILD = __DIR__ . '/build';
$GLOBALS['theme_dir'] = $BUILD . '/semzon-child';
$GLOBALS['did'] = array( 'elementor/loaded' );
define( 'ELEMENTOR_PRO_VERSION', '3.21' );

$fail = 0; $pass = 0;
function check( $label, $cond, $detail = '' ) {
	global $fail, $pass;
	if ( $cond ) { $pass++; echo "  PASS  $label\n"; }
	else { $fail++; echo "  FAIL  $label" . ( $detail ? "  << $detail" : '' ) . "\n"; }
}
function section( $t ) { echo "\n== $t ==\n"; }

// ============================================================ THEME LOADS
section( 'theme loads' );
require $BUILD . '/semzon-child/functions.php';
check( 'functions.php loaded without fatal', true );
check( 'Semzon_CPT defined by THEME', class_exists( 'Semzon_CPT' ) );
check( 'Semzon_ACF defined by THEME', class_exists( 'Semzon_ACF' ) );
check( 'Semzon_Admin defined by THEME', class_exists( 'Semzon_Admin' ) );

// fire the hooks WordPress would
do_action( 'after_setup_theme' );
do_action( 'init' );
do_action( 'acf/init' );
do_action( 'admin_menu' );
do_action( 'admin_init' );
do_action( 'wp_enqueue_scripts' );

section( 'post types & taxonomies (registered by the theme)' );
foreach ( array( 'product', 'solution', 'project' ) as $pt ) {
	check( "post type '$pt' registered", isset( $GLOBALS['post_types'][ $pt ] ) );
	check( "  '$pt' is public + has archive",
		! empty( $GLOBALS['post_types'][ $pt ]['public'] ) && ! empty( $GLOBALS['post_types'][ $pt ]['has_archive'] ) );
	check( "  '$pt' show_in_rest (Elementor dynamic needs it)",
		! empty( $GLOBALS['post_types'][ $pt ]['show_in_rest'] ) );
	check( "  '$pt' supports thumbnail",
		in_array( 'thumbnail', $GLOBALS['post_types'][ $pt ]['supports'], true ) );
}
check( 'taxonomy product_category registered', isset( $GLOBALS['taxonomies']['product_category'] ) );
check( 'taxonomy industry registered', isset( $GLOBALS['taxonomies']['industry'] ) );

section( 'ACF field groups (registered by the theme)' );
foreach ( array( 'group_semzon_product', 'group_semzon_solution', 'group_semzon_project', 'group_semzon_plant_flow' ) as $g ) {
	check( "field group $g registered", isset( $GLOBALS['acf_groups'][ $g ] ) );
}
check( 'plant flow options page registered', isset( $GLOBALS['acf_options']['semzon-plant-flow'] ) );
check( 'options page hangs off the THEME menu slug',
	( $GLOBALS['acf_options']['semzon-plant-flow']['parent_slug'] ?? '' ) === 'semzon',
	$GLOBALS['acf_options']['semzon-plant-flow']['parent_slug'] ?? 'none' );

// Collect every field name ACF knows about, recursively.
function collect_names( $fields, &$out ) {
	foreach ( $fields as $f ) {
		$out[] = $f['name'];
		if ( ! empty( $f['sub_fields'] ) ) { collect_names( $f['sub_fields'], $out ); }
	}
}
$known = array();
foreach ( $GLOBALS['acf_groups'] as $g ) { collect_names( $g['fields'], $known ); }
$known = array_unique( $known );

section( 'field-name contract: every name the importer writes must exist' );
$importer_src = file_get_contents( $BUILD . '/semzon-setup/includes/class-semzon-importer.php' );
preg_match_all( "/update_field\(\s*'([a-z_]+)'/", $importer_src, $m );
$written = array_unique( $m[1] );
foreach ( $written as $name ) {
	check( "importer writes '$name' -> field exists", in_array( $name, $known, true ) );
}

section( 'field-name contract: names the theme JS/PHP reads' );
foreach ( array( 'plant_flow_stages', 'machines', 'machine_name', 'machine_spec', 'linked_product',
                 'stage_number', 'stage_title', 'stage_description' ) as $name ) {
	check( "theme reads '$name' -> field exists", in_array( $name, $known, true ) );
}

section( 'image sizes match IMAGE-GUIDE ratios' );
$expect = array(
	'semzon-render'  => array( 1200, 900 ),
	'semzon-cutout'  => array( 1000, 1000 ),
	'semzon-project' => array( 1600, 1000 ),
	'semzon-portrait'=> array( 900, 600 ),
	'semzon-mega'    => array( 800, 600 ),
	'semzon-rail'    => array( 480, 600 ),
);
foreach ( $expect as $n => $wh ) {
	check( "image size $n = {$wh[0]}x{$wh[1]}",
		isset( $GLOBALS['image_sizes'][ $n ] )
		&& $GLOBALS['image_sizes'][ $n ][0] === $wh[0]
		&& $GLOBALS['image_sizes'][ $n ][1] === $wh[1] );
}

section( 'assets enqueued' );
check( 'design system stylesheet enqueued', isset( $GLOBALS['styles']['semzon-design-system'] ) );
check( 'parent Hello Elementor stylesheet enqueued', isset( $GLOBALS['styles']['hello-elementor'] ) );
check( 'semzon.js enqueued', isset( $GLOBALS['scripts']['semzon'] ) );
check( 'GSAP enqueued (CDN fallback since no local copy)', isset( $GLOBALS['scripts']['gsap'] ) );
check( 'ScrollTrigger enqueued', isset( $GLOBALS['scripts']['gsap-scrolltrigger'] ) );
check( 'SEMZON_DATA localized to the script', isset( $GLOBALS['localized']['SEMZON_DATA'] ) );
check( 'localized payload carries flow/mapsKey/mapPins',
	isset( $GLOBALS['localized']['SEMZON_DATA']['flow'],
	       $GLOBALS['localized']['SEMZON_DATA']['mapsKey'],
	       $GLOBALS['localized']['SEMZON_DATA']['mapPins'] ) );

section( 'admin menu ownership' );
check( 'theme owns the top-level "semzon" menu', in_array( 'semzon', $GLOBALS['admin_menu'] ?? array(), true ) );
check( 'Maps key setting registered by the THEME', isset( $GLOBALS['settings']['semzon_google_maps_key'] ) );
check( 'contact settings registered by the THEME', isset( $GLOBALS['settings']['semzon_phone'], $GLOBALS['settings']['semzon_email'] ) );

// snapshot what the theme alone provides
$theme_only = array(
	'post_types' => array_keys( $GLOBALS['post_types'] ),
	'taxonomies' => array_keys( $GLOBALS['taxonomies'] ),
	'acf_groups' => array_keys( $GLOBALS['acf_groups'] ),
	'settings'   => array_keys( $GLOBALS['settings'] ),
	'menu'       => $GLOBALS['admin_menu'] ?? array(),
);

// ============================================================ PLUGIN LOADS
section( 'installer plugin loads' );
require $BUILD . '/semzon-setup/semzon-setup.php';
do_action( 'plugins_loaded' );
do_action( 'admin_menu' );
check( 'Semzon_Wizard defined by PLUGIN', class_exists( 'Semzon_Wizard' ) );
check( 'Semzon_Importer defined by PLUGIN', class_exists( 'Semzon_Importer' ) );
check( 'Semzon_Elementor defined by PLUGIN', class_exists( 'Semzon_Elementor' ) );
check( 'wizard attached UNDER the theme menu',
	( $GLOBALS['admin_submenu']['semzon-setup'] ?? '' ) === 'semzon',
	$GLOBALS['admin_submenu']['semzon-setup'] ?? 'none' );

section( 'CRITICAL: plugin registers nothing the site depends on' );
check( 'plugin added no post types',
	array_keys( $GLOBALS['post_types'] ) === $theme_only['post_types'],
	json_encode( array_diff( array_keys( $GLOBALS['post_types'] ), $theme_only['post_types'] ) ) );
check( 'plugin added no taxonomies',
	array_keys( $GLOBALS['taxonomies'] ) === $theme_only['taxonomies'] );
check( 'plugin added no ACF field groups',
	array_keys( $GLOBALS['acf_groups'] ) === $theme_only['acf_groups'] );
check( 'plugin added no settings',
	array_keys( $GLOBALS['settings'] ) === $theme_only['settings'] );
$plugin_src = file_get_contents( $BUILD . '/semzon-setup/semzon-setup.php' );
check( 'plugin has no front-end hooks (wp_enqueue_scripts / template_redirect / the_content)',
	! preg_match( '/wp_enqueue_scripts|template_redirect|the_content|wp_head|wp_footer/', $plugin_src ) );
check( 'plugin bootstraps admin-only', (bool) preg_match( '/if \(\s*is_admin\(\)\s*\)/', $plugin_src ) );
check( 'uninstall.php exists', file_exists( $BUILD . '/semzon-setup/uninstall.php' ) );
$uninstall = file_get_contents( $BUILD . '/semzon-setup/uninstall.php' );
check( 'uninstall deletes ONLY its own flag',
	1 === preg_match_all( '/delete_option|wp_delete_post|delete_term|wp_delete_attachment/', $uninstall ) );

// ============================================================ WIZARD RUN
section( 'wizard steps run end to end' );
$steps = Semzon_Wizard::steps();
check( 'wizard has 8 steps', 8 === count( $steps ), (string) count( $steps ) );
foreach ( array_keys( $steps ) as $key ) {
	if ( 'elementor' === $key ) { continue; } // needs \Elementor\Plugin
	$r = Semzon_Wizard::run_step( $key );
	check( "step '$key' succeeded", ! empty( $r['success'] ), $r['message'] ?? '' );
}

section( 'content imported' );
$count = function ( $type ) {
	return count( array_filter( $GLOBALS['posts'], fn( $p ) => ( $p['post_type'] ?? '' ) === $type ) );
};
check( '22 products', 22 === $count( 'product' ), (string) $count( 'product' ) );
check( '8 solutions', 8 === $count( 'solution' ), (string) $count( 'solution' ) );
check( '6 projects', 6 === $count( 'project' ), (string) $count( 'project' ) );
check( '7 pages created', 7 === $count( 'page' ), (string) $count( 'page' ) );
check( 'front page set to Home', ! empty( $GLOBALS['options']['page_on_front'] ) );
check( 'show_on_front = page', 'page' === ( $GLOBALS['options']['show_on_front'] ?? '' ) );
check( 'map pins stored (7)', 7 === count( (array) get_option( 'semzon_map_locations', array() ) ) );
check( 'plant flow written to options', 6 === count( (array) get_field( 'plant_flow_stages' ) ) );
check( 'permalinks flushed', $GLOBALS['flushed'] > 0 );
check( 'setup marked finished', ! empty( get_option( 'semzon_setup_state' )['finished_at'] ) );

section( 'relationships resolved' );
$hammer = null;
foreach ( $GLOBALS['posts'] as $id => $p ) { if ( ( $p['post_name'] ?? '' ) === 'hammer-mill' ) { $hammer = $id; } }
check( 'hammer-mill imported', $hammer !== null );
$rel = $GLOBALS['fields'][ "$hammer:related_products" ] ?? array();
check( 'related_products = 3 integer IDs', 3 === count( $rel ) && is_int( $rel[0] ) );
check( 'category term assigned', array( 'Grinding' ) === ( $GLOBALS['terms'][ $hammer ] ?? array() ) );
$flow_rows = (array) get_field( 'plant_flow_stages' );
$all_m = array_merge( ...array_column( $flow_rows, 'machines' ) );
check( 'all 13 plant-flow machines linked to real products',
	0 === count( array_filter( $all_m, fn( $m ) => '' === $m['linked_product'] ) ) );

section( 'idempotency — re-running changes nothing' );
$before = count( $GLOBALS['posts'] );
foreach ( array( 'pages', 'products', 'solutions', 'projects', 'relations' ) as $key ) {
	Semzon_Wizard::run_step( $key );
}
check( 'no duplicate posts after second run', $before === count( $GLOBALS['posts'] ),
	"$before -> " . count( $GLOBALS['posts'] ) );

// ============================================================ DELETE PLUGIN
section( 'CRITICAL: simulate deleting the plugin after setup' );
$content_before = count( $GLOBALS['posts'] );

// uninstall.php runs, then a fresh request loads the THEME ONLY.
define( 'WP_UNINSTALL_PLUGIN', true );
require $BUILD . '/semzon-setup/uninstall.php';

$GLOBALS['post_types'] = array();
$GLOBALS['taxonomies'] = array();
$GLOBALS['acf_groups'] = array();
$GLOBALS['settings']   = array();
$GLOBALS['admin_menu'] = array();
$GLOBALS['hooks']      = array();

// Re-register from the theme classes alone (the plugin's files are gone).
Semzon_CPT::init();
Semzon_ACF::init();
Semzon_Admin::init();
do_action( 'init' );
do_action( 'acf/init' );
do_action( 'admin_menu' );
do_action( 'admin_init' );

check( 'products still registered', isset( $GLOBALS['post_types']['product'] ) );
check( 'solutions still registered', isset( $GLOBALS['post_types']['solution'] ) );
check( 'projects still registered', isset( $GLOBALS['post_types']['project'] ) );
check( 'taxonomies still registered', isset( $GLOBALS['taxonomies']['product_category'], $GLOBALS['taxonomies']['industry'] ) );
check( 'all 4 ACF groups still registered', 4 === count( $GLOBALS['acf_groups'] ) );
check( 'Plant Flow options page still registered', isset( $GLOBALS['acf_options']['semzon-plant-flow'] ) );
check( 'SEMZON menu still exists', in_array( 'semzon', $GLOBALS['admin_menu'], true ) );
check( 'Maps key setting still registered', isset( $GLOBALS['settings']['semzon_google_maps_key'] ) );
check( 'all content still in the database', $content_before === count( $GLOBALS['posts'] ) );
check( 'Maps key value survived', 'x' === ( $GLOBALS['options']['semzon_google_maps_key'] ?? 'x' ) );
check( 'map pins survived', 7 === count( (array) get_option( 'semzon_map_locations', array() ) ) );
check( 'plant flow data survived', 6 === count( (array) get_field( 'plant_flow_stages' ) ) );
check( 'only the setup flag was removed', ! isset( $GLOBALS['options']['semzon_setup_state'] ) );

// ============================================================ BREAKPOINTS
section( 'Elementor breakpoints' );
$bp = Semzon_Elementor::breakpoints();
$vals = array_values( $bp );
$sorted = $vals; sort( $sorted );
check( 'breakpoint values strictly ascend (Elementor requires it)', $vals === $sorted, json_encode( $bp ) );
check( 'six ranges declared', 6 === count( $bp ) );
check( 'mobile = 575 (< 576)', 575 === $bp['viewport_mobile'] );
check( 'mobile_extra = 767 (576-767)', 767 === $bp['viewport_mobile_extra'] );
check( 'tablet = 1023 (768-1023)', 1023 === $bp['viewport_tablet'] );
check( 'tablet_extra = 1279 (1024-1279 Laptop)', 1279 === $bp['viewport_tablet_extra'] );
check( 'laptop = 1535 (1280-1535 Laptop L)', 1535 === $bp['viewport_laptop'] );
check( 'widescreen = 1536 (1536+)', 1536 === $bp['viewport_widescreen'] );
check( 'system colours = design tokens',
	'#1C0863' === Semzon_Elementor::system_colors()[0]['color'] );

echo "\n" . str_repeat( '=', 58 ) . "\n";
echo $fail ? "$fail FAILED, $pass passed\n" : "ALL $pass CHECKS PASSED\n";
exit( $fail ? 1 : 0 );
