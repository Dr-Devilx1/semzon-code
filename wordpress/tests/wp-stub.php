<?php
/**
 * A WordPress stub broad enough to actually load the SEMZON theme and the
 * installer plugin and observe what each one registers.
 */

define( 'ABSPATH', '/tmp/fake-wp/' );

$GLOBALS['hooks']       = array();
$GLOBALS['post_types']  = array();
$GLOBALS['taxonomies']  = array();
$GLOBALS['acf_groups']  = array();
$GLOBALS['acf_options'] = array();
$GLOBALS['menus']       = array();
$GLOBALS['settings']    = array();
$GLOBALS['image_sizes'] = array();
$GLOBALS['scripts']     = array();
$GLOBALS['styles']      = array();
$GLOBALS['options']     = array();
$GLOBALS['posts']       = array();
$GLOBALS['fields']      = array();
$GLOBALS['terms']       = array();
$GLOBALS['thumbs']      = array();
$GLOBALS['flushed']     = 0;
$GLOBALS['next_id']     = 100;
$GLOBALS['theme_dir']   = '';

// ---- hooks ------------------------------------------------------------
function add_action( $tag, $cb, $prio = 10, $args = 1 ) { $GLOBALS['hooks'][ $tag ][] = $cb; return true; }
function add_filter( $tag, $cb, $prio = 10, $args = 1 ) { $GLOBALS['hooks'][ $tag ][] = $cb; return true; }
function do_action( $tag, ...$a ) {
	foreach ( $GLOBALS['hooks'][ $tag ] ?? array() as $cb ) { call_user_func_array( $cb, $a ); }
}
function apply_filters( $tag, $v, ...$a ) {
	foreach ( $GLOBALS['hooks'][ $tag ] ?? array() as $cb ) { $v = call_user_func( $cb, $v, ...$a ); }
	return $v;
}
function did_action( $tag ) { return in_array( $tag, $GLOBALS['did'] ?? array(), true ) ? 1 : 0; }
function has_action( $tag ) { return ! empty( $GLOBALS['hooks'][ $tag ] ); }
function register_activation_hook( $f, $cb ) { $GLOBALS['hooks']['__activate'][] = $cb; }
function register_deactivation_hook( $f, $cb ) { $GLOBALS['hooks']['__deactivate'][] = $cb; }

// ---- i18n / escaping --------------------------------------------------
function __( $s, $d = '' ) { return $s; }
function _e( $s, $d = '' ) { echo $s; }
function esc_html__( $s, $d = '' ) { return $s; }
function esc_attr__( $s, $d = '' ) { return $s; }
function esc_html_e( $s, $d = '' ) { echo $s; }
function esc_attr_e( $s, $d = '' ) { echo $s; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_url( $s ) { return (string) $s; }
function esc_url_raw( $s ) { return (string) $s; }
function wp_json_encode( $d, $f = 0 ) { return json_encode( $d, $f ); }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
function sanitize_file_name( $s ) { return (string) $s; }
function wp_unslash( $s ) { return $s; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function _n( $s, $p, $n, $d = '' ) { return 1 === $n ? $s : $p; }

// ---- registration -----------------------------------------------------
function register_post_type( $t, $a = array() ) { $GLOBALS['post_types'][ $t ] = $a; return (object) $a; }
function register_taxonomy( $t, $ot, $a = array() ) { $GLOBALS['taxonomies'][ $t ] = array( 'objects' => $ot ) + $a; }
function register_nav_menus( $m ) { $GLOBALS['menus'] += $m; }
function add_theme_support( $f, $a = null ) { return true; }
function add_image_size( $n, $w, $h, $c = false ) { $GLOBALS['image_sizes'][ $n ] = array( $w, $h, $c ); }
function register_setting( $g, $n, $a = array() ) { $GLOBALS['settings'][ $n ] = $a; }
function add_settings_section( ...$a ) {}
function add_settings_field( ...$a ) {}
function add_menu_page( $pt, $mt, $cap, $slug, $cb = '', $icon = '', $pos = null ) { $GLOBALS['admin_menu'][] = $slug; return $slug; }
function add_submenu_page( $parent, $pt, $mt, $cap, $slug, $cb = '' ) { $GLOBALS['admin_submenu'][ $slug ] = $parent; return $slug; }
function acf_add_local_field_group( $g ) { $GLOBALS['acf_groups'][ $g['key'] ] = $g; }
function acf_add_options_page( $a ) { $GLOBALS['acf_options'][ $a['menu_slug'] ] = $a; }

// ---- assets -----------------------------------------------------------
function wp_enqueue_style( $h, $src = '', $d = array(), $v = false, $m = 'all' ) { $GLOBALS['styles'][ $h ] = $src; }
function wp_enqueue_script( $h, $src = '', $d = array(), $v = false, $a = array() ) { $GLOBALS['scripts'][ $h ] = $src; }
function wp_localize_script( $h, $obj, $data ) { $GLOBALS['localized'][ $obj ] = $data; }
function get_template_directory_uri() { return 'http://x/wp-content/themes/hello-elementor'; }
function get_stylesheet_directory_uri() { return 'http://x/wp-content/themes/semzon-child'; }
function get_stylesheet_directory() { return $GLOBALS['theme_dir']; }
function get_template_directory() { return $GLOBALS['theme_dir']; }
function get_stylesheet() { return 'semzon-child'; }
function wp_get_theme( $s = null ) { return new class { public function get( $k ) { return 'SEMZON Engineering'; } }; }

// ---- options / posts --------------------------------------------------
function get_option( $k, $d = false ) { return $GLOBALS['options'][ $k ] ?? $d; }
function update_option( $k, $v, $a = null ) { $GLOBALS['options'][ $k ] = $v; return true; }
function add_option( $k, $v ) { if ( ! isset( $GLOBALS['options'][ $k ] ) ) { $GLOBALS['options'][ $k ] = $v; } return true; }
function delete_option( $k ) { unset( $GLOBALS['options'][ $k ] ); return true; }
function get_post_meta( $id, $k, $single = false ) { return $GLOBALS['meta'][ "$id:$k" ] ?? ( $single ? '' : array() ); }
function update_post_meta( $id, $k, $v ) { $GLOBALS['meta'][ "$id:$k" ] = $v; return true; }
function get_post( $id ) { return isset( $GLOBALS['posts'][ $id ] ) ? (object) $GLOBALS['posts'][ $id ] : null; }
function get_page_by_path( $p, $o = OBJECT, $t = 'page' ) {
	foreach ( $GLOBALS['posts'] as $id => $post ) {
		if ( ( $post['post_name'] ?? '' ) === $p && 'page' === ( $post['post_type'] ?? '' ) ) { return (object) ( $post + array( 'ID' => $id ) ); }
	}
	return null;
}
define( 'OBJECT', 'OBJECT' );

function get_posts( $args = array() ) {
	if ( ( $args['post_type'] ?? '' ) === 'attachment' ) { return array(); }
	if ( isset( $args['name'] ) ) {
		foreach ( $GLOBALS['posts'] as $id => $p ) {
			if ( ( $p['post_name'] ?? '' ) === $args['name'] && ( $p['post_type'] ?? '' ) === $args['post_type'] ) { return array( $id ); }
		}
		return array();
	}
	$out = array();
	foreach ( $GLOBALS['posts'] as $id => $p ) {
		if ( ( $p['post_type'] ?? '' ) === ( $args['post_type'] ?? '' ) ) { $out[] = (object) ( $p + array( 'ID' => $id ) ); }
	}
	return $out;
}
function wp_insert_post( $a, $e = false ) { $id = $GLOBALS['next_id']++; $GLOBALS['posts'][ $id ] = $a; return $id; }
function wp_update_post( $a, $e = false ) { $GLOBALS['posts'][ $a['ID'] ] = $a; return $a['ID']; }
function wp_set_object_terms( $id, $t, $tax, $ap ) { $GLOBALS['terms'][ $id ] = $t; }
function set_post_thumbnail( $id, $att ) { $GLOBALS['thumbs'][ $id ] = $att; }
function get_post_thumbnail_id( $p = null ) { return $GLOBALS['thumbs'][ is_object( $p ) ? $p->ID : $p ] ?? 0; }
function get_permalink( $p ) { $id = is_object( $p ) ? $p->ID : $p; return 'http://x/?p=' . $id; }
function get_the_title( $p ) { $id = is_object( $p ) ? $p->ID : $p; return $GLOBALS['posts'][ $id ]['post_title'] ?? ''; }
function get_post_type_archive_link( $t ) { return "http://x/$t/"; }
function get_term_link( $t ) { return 'http://x/term/'; }
function get_terms( $a = array() ) { return array(); }
function is_wp_error( $t ) { return $t instanceof WP_Error; }
class WP_Error {}
class WP_Post {}
function flush_rewrite_rules( $hard = true ) { $GLOBALS['flushed']++; }
function current_time( $t ) { return '2026-08-27 00:00:00'; }
function home_url( $p = '/' ) { return 'http://x' . $p; }
function admin_url( $p = '' ) { return 'http://x/wp-admin/' . $p; }
function is_admin() { return true; }
function is_404() { return false; }
function current_user_can( $c ) { return true; }
function wp_create_nonce( $a ) { return 'nonce'; }
function check_ajax_referer( ...$a ) { return true; }
function wp_send_json_success( $d = null ) { throw new RuntimeException( 'json_success' ); }
function wp_send_json_error( $d = null, $c = null ) { throw new RuntimeException( 'json_error' ); }
function wp_safe_redirect( $u, $s = 302 ) { return true; }
function get_bloginfo( $s = '' ) { return 'SEMZON'; }
function language_attributes() { echo 'lang="en"'; }
function body_class( $c = '' ) { echo ''; }
function wp_head() {}
function wp_footer() {}
function wp_body_open() { do_action( 'wp_body_open' ); }
function get_theme_mod( $k, $d = false ) { return $d; }
function wp_get_attachment_image( ...$a ) { return '<img>'; }
function has_nav_menu( $l ) { return false; }
function get_nav_menu_locations() { return array(); }
function wp_get_nav_menu_object( $m ) { return null; }
function wp_get_nav_menu_items( $id ) { return array(); }
function elementor_theme_do_location( $l ) { return false; }
function submit_button( ...$a ) {}
function settings_fields( $g ) {}
function do_settings_sections( $p ) {}
function get_current_screen() { return null; }
function have_rows( $f, $p = false ) { return false; }
function the_row() {}
function get_sub_field( $f ) { return ''; }
function update_field( $n, $v, $id = false ) { $GLOBALS['fields'][ ( false === $id ? 'option' : $id ) . ":$n" ] = $v; return true; }
function get_field( $n, $id = false ) { return $GLOBALS['fields'][ ( false === $id ? 'option' : $id ) . ":$n" ] ?? null; }
function plugin_dir_path( $f ) { return dirname( $f ) . '/'; }
function plugin_dir_url( $f ) { return 'http://x/wp-content/plugins/semzon-setup/'; }
function wp_upload_bits( $n, $x, $b ) { return array( 'file' => '/tmp/up/' . $n ); }
function wp_check_filetype( $f, $m = null ) { return array( 'type' => 'image/webp' ); }
function wp_insert_attachment( $a, $f ) { return $GLOBALS['next_id']++; }
function wp_generate_attachment_metadata( $i, $f ) { return array(); }
function wp_update_attachment_metadata( $i, $m ) { return true; }
function __return_false() { return false; }
function screen_reader_text() {}
function filemtime_safe( $p ) { return 1; }
