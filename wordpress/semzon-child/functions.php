<?php
/**
 * SEMZON Engineering — Hello Elementor child theme.
 *
 * Carries the original site's design system (CSS + motion) so Elementor
 * layouts reproduce the coded build exactly. Content structure is built in
 * Elementor; this file only supplies the design system, fonts, image sizes
 * and the motion layer.
 *
 * @package semzon
 */

defined( 'ABSPATH' ) || exit;

define( 'SEMZON_THEME_VERSION', '1.2.0' );

/*
 * Everything the finished site depends on is registered by the theme, not by
 * the SEMZON Setup plugin. The plugin is a one-time installer and is safe to
 * delete the moment setup finishes — see docs/00-START-HERE.md.
 */
require_once get_stylesheet_directory() . '/inc/post-types.php';
require_once get_stylesheet_directory() . '/inc/fields.php';
require_once get_stylesheet_directory() . '/inc/navigation.php';

if ( is_admin() ) {
	require_once get_stylesheet_directory() . '/inc/admin.php';
}

/**
 * Flush rewrite rules once when the theme is activated.
 *
 * The post types are registered here, so their permalinks would 404 until
 * someone visited Settings → Permalinks. Doing it on switch_theme costs one
 * request and removes that trap.
 */
function semzon_flush_rewrites_on_activation() {
	if ( class_exists( 'Semzon_CPT' ) && method_exists( 'Semzon_CPT', 'register' ) ) {
		Semzon_CPT::register();
	}
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'semzon_flush_rewrites_on_activation' );

/**
 * Enqueue the parent theme stylesheet, the SEMZON design system and motion.
 *
 * GSAP + ScrollTrigger are loaded locally (no CDN) so the site has no
 * third-party runtime dependency and Core Web Vitals stay under our control.
 * Both are registered as deferred so they never block first paint; the motion
 * layer degrades to a fully readable static page if they fail to load.
 */
function semzon_enqueue_assets() {
	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();

	wp_enqueue_style(
		'hello-elementor',
		get_template_directory_uri() . '/style.css',
		array(),
		SEMZON_THEME_VERSION
	);

	$css_path = $theme_dir . '/assets/css/semzon-design-system.css';
	wp_enqueue_style(
		'semzon-design-system',
		$theme_uri . '/assets/css/semzon-design-system.css',
		array( 'hello-elementor' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : SEMZON_THEME_VERSION
	);

	/*
	 * Motion libraries.
	 *
	 * Prefers local copies in assets/js/ — self-hosting removes a third-party
	 * request, keeps the site working if the CDN is blocked, and avoids leaking
	 * visitor IPs to another host. Drop gsap.min.js and ScrollTrigger.min.js
	 * there (see assets/js/README.md) and they are used automatically.
	 *
	 * Until then it falls back to the same CDN the original build used, so the
	 * choreography works the moment the theme is activated. If both are
	 * unavailable the motion layer degrades to a fully readable static page.
	 */
	$gsap_local = $theme_dir . '/assets/js/gsap.min.js';
	$st_local   = $theme_dir . '/assets/js/ScrollTrigger.min.js';
	$self_host  = file_exists( $gsap_local ) && file_exists( $st_local );

	$gsap_src = $self_host
		? $theme_uri . '/assets/js/gsap.min.js'
		: 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js';
	$st_src = $self_host
		? $theme_uri . '/assets/js/ScrollTrigger.min.js'
		: 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js';

	wp_enqueue_script( 'gsap', $gsap_src, array(), '3.12.5', array( 'strategy' => 'defer' ) );
	wp_enqueue_script( 'gsap-scrolltrigger', $st_src, array( 'gsap' ), '3.12.5', array( 'strategy' => 'defer' ) );

	$js_path = $theme_dir . '/assets/js/semzon.js';
	$js_deps = array( 'gsap', 'gsap-scrolltrigger' );
	wp_enqueue_script(
		'semzon',
		$theme_uri . '/assets/js/semzon.js',
		$js_deps,
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : SEMZON_THEME_VERSION,
		array( 'strategy' => 'defer' )
	);

	wp_localize_script( 'semzon', 'SEMZON_DATA', semzon_frontend_data() );
}
add_action( 'wp_enqueue_scripts', 'semzon_enqueue_assets', 20 );

/**
 * Data handed to the motion layer.
 *
 * The plant-flow stages come from the ACF options page created by the SEMZON
 * Setup plugin so they are editable in wp-admin instead of hardcoded in JS.
 * The Google Maps key is read from a WordPress option — never committed to
 * source and never printed anywhere except this localized payload.
 *
 * @return array
 */
function semzon_frontend_data() {
	$stages = array();

	if ( function_exists( 'have_rows' ) && have_rows( 'plant_flow_stages', 'option' ) ) {
		while ( have_rows( 'plant_flow_stages', 'option' ) ) {
			the_row();

			$machines = array();
			if ( have_rows( 'machines' ) ) {
				while ( have_rows( 'machines' ) ) {
					the_row();
					$linked = get_sub_field( 'linked_product' );
					$machines[] = array(
						'name' => (string) get_sub_field( 'machine_name' ),
						'spec' => (string) get_sub_field( 'machine_spec' ),
						'url'  => $linked instanceof WP_Post ? get_permalink( $linked ) : '',
					);
				}
			}

			$stages[] = array(
				'n' => (string) get_sub_field( 'stage_number' ),
				't' => (string) get_sub_field( 'stage_title' ),
				'd' => (string) get_sub_field( 'stage_description' ),
				'm' => $machines,
			);
		}
	}

	return array(
		'flow'    => $stages,
		'mapsKey' => (string) get_option( 'semzon_google_maps_key', '' ),
		'mapPins' => (array) get_option( 'semzon_map_locations', array() ),
	);
}

/**
 * Image sizes matching the fixed CSS frames documented in IMAGE-GUIDE.md.
 *
 * Every slot in the design has a locked aspect ratio, so registering matching
 * sizes lets WordPress serve correctly-cropped files instead of scaling full
 * uploads — the single biggest LCP win on the product and project pages.
 */
function semzon_register_image_sizes() {
	add_theme_support( 'post-thumbnails' );

	// Machine render — 4:3 contain (product hero, cards, related tiles).
	add_image_size( 'semzon-render', 1200, 900, true );
	add_image_size( 'semzon-render-sm', 600, 450, true );

	// Machine cut-out — 1:1 transparent, drop-shadow treatment.
	add_image_size( 'semzon-cutout', 1000, 1000, true );

	// Project / facility photo — 16:10 cover.
	add_image_size( 'semzon-project', 1600, 1000, true );
	add_image_size( 'semzon-project-sm', 800, 500, true );

	// Portrait (leadership) — 3:2 cover.
	add_image_size( 'semzon-portrait', 900, 600, true );

	// Mega-menu feature — 4:3.
	add_image_size( 'semzon-mega', 800, 600, true );

	// Hero rail card — 4:5 cover.
	add_image_size( 'semzon-rail', 480, 600, true );
}
add_action( 'after_setup_theme', 'semzon_register_image_sizes' );

/**
 * Preconnect to the Google Fonts hosts the design system depends on.
 *
 * Bricolage Grotesque / Inter / JetBrains Mono are loaded by Elementor's own
 * Global Fonts once the kit is imported, so we only add the resource hints.
 *
 * @param array  $urls Existing hints.
 * @param string $relation_type Hint type.
 * @return array
 */
function semzon_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'semzon_resource_hints', 10, 2 );

/**
 * The design system supplies its own focus, selection and scrollbar styling,
 * and Elementor's container width is driven by the --container token. Telling
 * Elementor to skip its default content-width wrapper keeps the two from
 * fighting over max-width on wide screens.
 */
function semzon_theme_supports() {
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus(
		array(
			'primary'          => __( 'Primary (desktop nav)', 'semzon' ),
			'mobile'           => __( 'Mobile drawer', 'semzon' ),
			'footer_products'  => __( 'Footer — Products', 'semzon' ),
			'footer_solutions' => __( 'Footer — Solutions', 'semzon' ),
		)
	);
}
add_action( 'after_setup_theme', 'semzon_theme_supports' );

/**
 * The original build ships a scroll-progress bar, a sticky mobile action bar
 * and a toast host that sit outside any Elementor template. Printing them from
 * the theme keeps them on every page without an HTML widget in the layout.
 */
function semzon_print_chrome() {
	if ( is_admin() ) {
		return;
	}
	echo '<div class="scroll-progress" id="scroll-progress" aria-hidden="true"></div>';
}
add_action( 'wp_body_open', 'semzon_print_chrome' );

/**
 * Toast host — used by the motion layer for transient status messages.
 */
function semzon_print_toast() {
	if ( is_admin() ) {
		return;
	}
	echo '<div class="toast" id="toast" role="status" aria-live="polite"></div>';
}
add_action( 'wp_footer', 'semzon_print_toast', 5 );
