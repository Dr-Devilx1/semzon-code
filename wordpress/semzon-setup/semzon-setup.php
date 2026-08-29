<?php
/**
 * Plugin Name:       SEMZON Setup (one-time installer)
 * Plugin URI:        https://www.semzoneng.com/
 * Description:       One-time installer for the SEMZON site. Imports the catalogue content and images, writes the design tokens and the six responsive breakpoints into the Elementor kit, and creates the required pages. It registers nothing the finished site depends on — post types, custom fields and settings all live in the SEMZON Engineering theme — so once the wizard reports "complete" this plugin can be deleted with no effect on the website.
 * Version:           1.1.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            SEMZON Engineering
 * License:           GPL-2.0-or-later
 * Text Domain:       semzon-setup
 *
 * @package semzon-setup
 */

defined( 'ABSPATH' ) || exit;

define( 'SEMZON_SETUP_VERSION', '1.1.0' );
define( 'SEMZON_SETUP_DIR', plugin_dir_path( __FILE__ ) );

require_once SEMZON_SETUP_DIR . 'includes/class-semzon-elementor.php';
require_once SEMZON_SETUP_DIR . 'includes/class-semzon-importer.php';
require_once SEMZON_SETUP_DIR . 'includes/class-semzon-wizard.php';

/**
 * Boot the installer.
 *
 * Admin only, by design: this plugin has no front-end behaviour whatsoever.
 * Nothing here runs on a visitor request, which is also why removing it cannot
 * change how the site renders.
 */
function semzon_setup_bootstrap() {
	if ( is_admin() ) {
		Semzon_Wizard::init();
	}
}
add_action( 'plugins_loaded', 'semzon_setup_bootstrap' );

/**
 * Requirements notice.
 *
 * The theme is the hard dependency — it owns the post types the importer
 * writes into. Elementor Pro and ACF PRO are needed for the site itself.
 */
function semzon_setup_requirements_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && ! in_array( $screen->id, array( 'plugins', 'toplevel_page_semzon', 'semzon_page_semzon-setup' ), true ) ) {
		return;
	}

	$missing = array();

	if ( ! class_exists( 'Semzon_CPT' ) ) {
		$missing[] = __( 'the SEMZON Engineering theme (activate it under Appearance → Themes)', 'semzon-setup' );
	}
	if ( ! did_action( 'elementor/loaded' ) ) {
		$missing[] = 'Elementor';
	}
	if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
		$missing[] = 'Elementor Pro';
	}
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		$missing[] = __( 'Advanced Custom Fields PRO (the free build cannot do repeater fields, which this site needs)', 'semzon-setup' );
	}

	if ( ! $missing ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p><strong>SEMZON Setup:</strong> %s</p></div>',
		esc_html(
			sprintf(
				/* translators: %s: comma separated list of missing requirements */
				__( 'not ready to run — missing %s.', 'semzon-setup' ),
				implode( '; ', $missing )
			)
		)
	);
}
add_action( 'admin_notices', 'semzon_setup_requirements_notice' );

/**
 * Record install time so the wizard can show whether it has been run.
 */
function semzon_setup_activate() {
	if ( ! get_option( 'semzon_setup_state' ) ) {
		add_option( 'semzon_setup_state', array( 'finished_at' => '' ) );
	}
}
register_activation_hook( __FILE__, 'semzon_setup_activate' );

/**
 * Deactivation is a no-op.
 *
 * There is deliberately nothing to tear down: no post types to unregister, no
 * fields to remove, no rewrite rules of its own. The site does not notice.
 */
