<?php
/**
 * Plugin Name:       SEMZON Setup
 * Plugin URI:        https://www.semzoneng.com/
 * Description:       Installer and content architecture for the SEMZON site. Registers the Product, Solution and Project post types, their taxonomy and ACF field groups, configures Elementor's global colours, fonts and responsive breakpoints, and seeds the catalogue content. This is a configurator, not a rendering engine — once setup is finished the site runs as a normal WordPress + Elementor + ACF install and this plugin only maintains the registrations.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            SEMZON Engineering
 * License:           GPL-2.0-or-later
 * Text Domain:       semzon-setup
 *
 * @package semzon-setup
 */

defined( 'ABSPATH' ) || exit;

define( 'SEMZON_SETUP_VERSION', '1.0.0' );
define( 'SEMZON_SETUP_FILE', __FILE__ );
define( 'SEMZON_SETUP_DIR', plugin_dir_path( __FILE__ ) );
define( 'SEMZON_SETUP_URL', plugin_dir_url( __FILE__ ) );

require_once SEMZON_SETUP_DIR . 'includes/class-semzon-cpt.php';
require_once SEMZON_SETUP_DIR . 'includes/class-semzon-acf.php';
require_once SEMZON_SETUP_DIR . 'includes/class-semzon-elementor.php';
require_once SEMZON_SETUP_DIR . 'includes/class-semzon-importer.php';
require_once SEMZON_SETUP_DIR . 'includes/class-semzon-settings.php';
require_once SEMZON_SETUP_DIR . 'includes/class-semzon-wizard.php';

/**
 * Registrations must run on every load, not just during setup — the post
 * types and fields are what the site is built on. Only the wizard UI and the
 * importer are one-time operations.
 */
function semzon_setup_bootstrap() {
	Semzon_CPT::init();
	Semzon_ACF::init();
	Semzon_Elementor::init();
	Semzon_Settings::init();

	if ( is_admin() ) {
		Semzon_Wizard::init();
	}
}
add_action( 'plugins_loaded', 'semzon_setup_bootstrap' );

/**
 * Flush rewrite rules once on activation so the CPT permalinks resolve
 * immediately instead of 404ing until someone visits Settings → Permalinks.
 */
function semzon_setup_activate() {
	Semzon_CPT::register();
	flush_rewrite_rules();

	if ( ! get_option( 'semzon_setup_state' ) ) {
		add_option(
			'semzon_setup_state',
			array(
				'post_types'  => false,
				'taxonomies'  => false,
				'acf'         => false,
				'elementor'   => false,
				'pages'       => false,
				'content'     => false,
				'finished_at' => '',
			)
		);
	}
}
register_activation_hook( __FILE__, 'semzon_setup_activate' );

/**
 * Leave the content in place on deactivation — posts, fields and pages are the
 * client's data. Only the rewrite cache is cleared.
 */
function semzon_setup_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'semzon_setup_deactivate' );

/**
 * Dependency notice. The build depends on Elementor Pro (Theme Builder +
 * dynamic tags) and ACF PRO (repeater fields). Without them the site will not
 * render the templates, so say so plainly rather than failing quietly.
 */
function semzon_setup_dependency_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$missing = array();

	if ( ! did_action( 'elementor/loaded' ) ) {
		$missing[] = 'Elementor';
	}
	if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
		$missing[] = 'Elementor Pro';
	}
	if ( ! class_exists( 'ACF' ) ) {
		$missing[] = 'Advanced Custom Fields PRO';
	} elseif ( ! function_exists( 'acf_add_local_field_group' ) ) {
		$missing[] = 'Advanced Custom Fields PRO (the free build is active — repeater fields are required)';
	}

	if ( ! $missing ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p><strong>SEMZON Setup:</strong> %s</p></div>',
		esc_html( sprintf(
			/* translators: %s: comma separated plugin names */
			__( 'the following required plugins are not active: %s. Activate them, then run SEMZON → Setup Wizard.', 'semzon-setup' ),
			implode( ', ', $missing )
		) )
	);
}
add_action( 'admin_notices', 'semzon_setup_dependency_notice' );
