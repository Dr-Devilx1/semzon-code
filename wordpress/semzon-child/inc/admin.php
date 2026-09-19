<?php
/**
 * SEMZON admin menu and site settings.
 *
 * Lives in the theme, not the installer plugin, so that deleting the plugin
 * after setup leaves the Maps key, the Plant Flow editor and the menu they sit
 * under fully intact.
 *
 * The Google Maps key is stored as a WordPress option rather than printed into
 * the page source. In the original static build it was committed into all 46
 * HTML files, so anyone could read it and spend the account's quota.
 * Restricting the key by HTTP referrer in the Google Cloud console is still
 * required — that, not secrecy, is what actually stops another site using it.
 *
 * @package semzon
 */

defined( 'ABSPATH' ) || exit;

/*
 * Guard against a duplicate declaration.
 *
 * An earlier build shipped some of these classes in the installer plugin
 * rather than the theme, so a stale copy of that plugin would trigger a fatal
 * "cannot redeclare" and take the whole site down.
 *
 * This has to be an if/endif wrapper, not an early `return`: PHP binds an
 * unconditional top-level class when the file is *compiled*, before any
 * statement in it runs — so a `return` guard would find the class already
 * present, bail, and skip the registration call at the bottom of the file,
 * leaving the site with no post types and no fields. Wrapping the declaration
 * defers binding to runtime, so the guard only fires for a genuine duplicate.
 */
if ( ! class_exists( 'Semzon_Admin' ) ) :

/**
 * Top-level menu, settings registration and the settings screen.
 */
class Semzon_Admin {

	const CAPABILITY = 'manage_options';

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 9 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * The SEMZON top-level menu.
	 *
	 * Registered at priority 9 so it exists before the installer plugin (which
	 * hooks at the default 10) tries to attach its wizard as a submenu.
	 */
	public static function menu() {
		add_menu_page(
			__( 'SEMZON', 'semzon' ),
			__( 'SEMZON', 'semzon' ),
			self::CAPABILITY,
			'semzon',
			array( __CLASS__, 'render_settings' ),
			'dashicons-admin-generic',
			58
		);

		add_submenu_page(
			'semzon',
			__( 'Settings', 'semzon' ),
			__( 'Settings', 'semzon' ),
			self::CAPABILITY,
			'semzon',
			array( __CLASS__, 'render_settings' )
		);
	}

	/**
	 * Every editable site option.
	 *
	 * @return array<string,array{label:string,default:string,description:string}>
	 */
	public static function options() {
		return array(
			'semzon_google_maps_key' => array(
				'label'       => __( 'Google Maps API key', 'semzon' ),
				'default'     => '',
				'description' => __( 'Leave empty to keep the styled fallback map graphic. Any key pasted here must be restricted by HTTP referrer to this domain in the Google Cloud console.', 'semzon' ),
			),
			'semzon_phone'           => array(
				'label'       => __( 'Phone', 'semzon' ),
				'default'     => '+92 323 8845411',
				'description' => '',
			),
			'semzon_whatsapp'        => array(
				'label'       => __( 'WhatsApp number', 'semzon' ),
				'default'     => '923238845411',
				'description' => __( 'Digits only, including country code.', 'semzon' ),
			),
			'semzon_email'           => array(
				'label'       => __( 'Email', 'semzon' ),
				'default'     => 'semzoneng@gmail.com',
				'description' => '',
			),
			'semzon_address_line'    => array(
				'label'       => __( 'Address line', 'semzon' ),
				'default'     => '2.5 KM Manga Raiwind Road',
				'description' => '',
			),
			'semzon_address_city'    => array(
				'label'       => __( 'Address city', 'semzon' ),
				'default'     => 'Manga Mandi, Lahore, Pakistan',
				'description' => '',
			),
			'semzon_util_note'       => array(
				'label'       => __( 'Utility bar note', 'semzon' ),
				'default'     => 'ISO 9001:2015 · MANGA RAIWIND ROAD, LAHORE',
				'description' => '',
			),
		);
	}

	/**
	 * Register each option with the Settings API.
	 */
	public static function register_settings() {
		add_settings_section(
			'semzon_main',
			__( 'Site details', 'semzon' ),
			'__return_false',
			'semzon-settings'
		);

		foreach ( self::options() as $name => $config ) {
			register_setting(
				'semzon_settings',
				$name,
				array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => $config['default'],
				)
			);

			add_settings_field(
				$name,
				$config['label'],
				array( __CLASS__, 'render_field' ),
				'semzon-settings',
				'semzon_main',
				array( 'name' => $name )
			);
		}
	}

	/**
	 * Render one text field.
	 *
	 * @param array $args Field args carrying the option name.
	 */
	public static function render_field( $args ) {
		$name    = $args['name'];
		$config  = self::options()[ $name ];
		$value   = get_option( $name, $config['default'] );
		$is_key  = 'semzon_google_maps_key' === $name;

		printf(
			'<input type="text" name="%1$s" id="%1$s" value="%2$s" class="regular-text"%3$s />',
			esc_attr( $name ),
			esc_attr( (string) $value ),
			$is_key ? ' autocomplete="off" spellcheck="false"' : ''
		);

		if ( $config['description'] ) {
			printf( '<p class="description">%s</p>', esc_html( $config['description'] ) );
		}
	}

	/**
	 * Settings screen.
	 */
	public static function render_settings() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SEMZON Settings', 'semzon' ); ?></h1>
			<p style="max-width:70ch">
				<?php esc_html_e( 'These values feed the header utility bar, the mobile action bar, the footer and the contact links across the whole site — change them here rather than editing any template.', 'semzon' ); ?>
			</p>
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

Semzon_Admin::init();

endif;
