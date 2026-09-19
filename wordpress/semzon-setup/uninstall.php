<?php
/**
 * Uninstall — runs when the installer plugin is deleted from wp-admin.
 *
 * It removes only this plugin's own bookkeeping flag. Everything the site is
 * made of is deliberately left alone:
 *
 *   - products, solutions, projects and their custom field values
 *   - taxonomy terms
 *   - images in the media library
 *   - the pages the wizard created
 *   - Elementor's kit settings, breakpoints and templates
 *   - the SEMZON site settings (contact details, Maps key)
 *
 * None of those belong to the installer. Deleting this plugin after setup is
 * the intended end state, and the website must not change when it happens.
 *
 * @package semzon-setup
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'semzon_setup_state' );
