<?php
/**
 * Site settings that do not belong in a post — API keys and contact details.
 *
 * The Google Maps key lives here rather than in the page source. In the
 * original static build it was committed into all 46 HTML files, which meant
 * anyone could read it from the page and spend the account's quota. Storing it
 * as an option keeps it out of version control; restricting it by HTTP
 * referrer in the Google Cloud console is still required and is the only thing
 * that actually stops another site from using it.
 *
 * @package semzon-setup
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings screen.
 */
class Semzon_Settings {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register options and fields.
	 */
	public static function register() {
		register_setting(
			'semzon_settings',
			'semzon_google_maps_key',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		add_settings_section(
			'semzon_keys',
			__( 'API keys', 'semzon-setup' ),
			array( __CLASS__, 'section_intro' ),
			'semzon-settings'
		);

		add_settings_field(
			'semzon_google_maps_key',
			__( 'Google Maps API key', 'semzon-setup' ),
			array( __CLASS__, 'field_maps_key' ),
			'semzon-settings',
			'semzon_keys'
		);
	}

	/**
	 * Section description.
	 */
	public static function section_intro() {
		echo '<p>' . esc_html__(
			'Leave the key empty to keep the styled fallback map graphic. Any key you paste here must be restricted by HTTP referrer to this domain in the Google Cloud console — an unrestricted key can be copied from the page and used by anyone.',
			'semzon-setup'
		) . '</p>';
	}

	/**
	 * Render the key field.
	 */
	public static function field_maps_key() {
		$value = (string) get_option( 'semzon_google_maps_key', '' );
		printf(
			'<input type="text" name="semzon_google_maps_key" value="%s" class="regular-text" autocomplete="off" spellcheck="false" />',
			esc_attr( $value )
		);
		echo '<p class="description">' . esc_html__( 'Used only by the Global Presence map on the contact and home pages.', 'semzon-setup' ) . '</p>';
	}

	/**
	 * Render the settings page body.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SEMZON Settings', 'semzon-setup' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'semzon_settings' );
				do_settings_sections( 'semzon-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
