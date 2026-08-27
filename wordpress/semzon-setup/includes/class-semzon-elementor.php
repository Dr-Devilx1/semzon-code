<?php
/**
 * Elementor kit configuration — breakpoints, global colours, global fonts.
 *
 * These are written into the active Elementor kit so the editor's device
 * switcher, the global colour picker and the design system stylesheet all
 * describe the same design. Writing them programmatically means a rebuild or
 * a fresh install lands on the exact same values rather than someone
 * re-typing hex codes.
 *
 * @package semzon-setup
 */

defined( 'ABSPATH' ) || exit;

/**
 * Applies the SEMZON design tokens to Elementor's global settings.
 */
class Semzon_Elementor {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'elementor/theme/register_locations', array( __CLASS__, 'register_locations' ) );
	}

	/**
	 * Enable Theme Builder locations so header/footer/single templates can be
	 * assigned. Hello Elementor registers these itself, but a child theme that
	 * overrides the templates needs them declared explicitly.
	 *
	 * @param object $manager Elementor locations manager.
	 */
	public static function register_locations( $manager ) {
		if ( is_object( $manager ) && method_exists( $manager, 'register_all_core_location' ) ) {
			$manager->register_all_core_location();
		}
	}

	/**
	 * The six responsive ranges required by the build.
	 *
	 * Elementor's slots must ascend in its own fixed order, so the design's
	 * "Laptop" range lands in the tablet_extra slot and "Laptop L" in the
	 * laptop slot. The values are the maximum width of each range, except
	 * widescreen which is a minimum.
	 *
	 * @return array<string,int>
	 */
	public static function breakpoints() {
		return array(
			'viewport_mobile'       => 575,   // Mobile portrait: below 576.
			'viewport_mobile_extra' => 767,   // Mobile landscape: 576–767.
			'viewport_tablet'       => 1023,  // Tablet: 768–1023.
			'viewport_tablet_extra' => 1279,  // Laptop: 1024–1279.
			'viewport_laptop'       => 1535,  // Laptop L: 1280–1535.
			'viewport_widescreen'   => 1536,  // Wide screen: 1536 and up.
		);
	}

	/**
	 * Global colours, taken straight from the design system tokens.
	 *
	 * @return array
	 */
	public static function system_colors() {
		return array(
			array(
				'_id'   => 'primary',
				'title' => __( 'Brand 800', 'semzon-setup' ),
				'color' => '#1C0863',
			),
			array(
				'_id'   => 'secondary',
				'title' => __( 'Brand 600', 'semzon-setup' ),
				'color' => '#3A22A8',
			),
			array(
				'_id'   => 'text',
				'title' => __( 'Text 900', 'semzon-setup' ),
				'color' => '#14152B',
			),
			array(
				'_id'   => 'accent',
				'title' => __( 'Accent 700', 'semzon-setup' ),
				'color' => '#840C0C',
			),
		);
	}

	/**
	 * The rest of the palette, exposed to the editor as custom globals.
	 *
	 * @return array
	 */
	public static function custom_colors() {
		$palette = array(
			'ink_950'      => array( 'Ink 950', '#0D0333' ),
			'ink_900'      => array( 'Ink 900', '#140547' ),
			'brand_700'    => array( 'Brand 700', '#2A1080' ),
			'brand_400'    => array( 'Brand 400', '#8A79CC' ),
			'brand_200'    => array( 'Brand 200', '#D5CFEE' ),
			'brand_100'    => array( 'Brand 100', '#EBE8F7' ),
			'brand_050'    => array( 'Brand 050', '#F5F4FB' ),
			'accent_600'   => array( 'Accent 600', '#9E1414' ),
			'paper'        => array( 'Paper', '#FFFFFF' ),
			'mist'         => array( 'Mist', '#F6F7F9' ),
			'line'         => array( 'Line', '#E4E6EB' ),
			'line_dk'      => array( 'Line dark', '#2B2260' ),
			'text_700'     => array( 'Text 700', '#2B2C42' ),
			'text_500'     => array( 'Text 500', '#4C4D60' ),
			'text_inv'     => array( 'Text inverse soft', '#C7C2E8' ),
		);

		$out = array();
		foreach ( $palette as $id => $pair ) {
			$out[] = array(
				'_id'   => $id,
				'title' => $pair[0],
				'color' => $pair[1],
			);
		}
		return $out;
	}

	/**
	 * Global typography mapped to the three type families in the design.
	 *
	 * Sizes use the same clamp() values as the stylesheet so the editor
	 * preview and the front end agree at every viewport.
	 *
	 * @return array
	 */
	public static function system_typography() {
		return array(
			array(
				'_id'                    => 'primary',
				'title'                  => __( 'Display', 'semzon-setup' ),
				'typography_typography'  => 'custom',
				'typography_font_family' => 'Bricolage Grotesque',
				'typography_font_weight' => '800',
				'typography_line_height' => array(
					'unit' => 'em',
					'size' => 1.08,
				),
				'typography_letter_spacing' => array(
					'unit' => 'em',
					'size' => -0.022,
				),
			),
			array(
				'_id'                    => 'secondary',
				'title'                  => __( 'Display — section', 'semzon-setup' ),
				'typography_typography'  => 'custom',
				'typography_font_family' => 'Bricolage Grotesque',
				'typography_font_weight' => '700',
				'typography_line_height' => array(
					'unit' => 'em',
					'size' => 1.12,
				),
			),
			array(
				'_id'                    => 'text',
				'title'                  => __( 'Body', 'semzon-setup' ),
				'typography_typography'  => 'custom',
				'typography_font_family' => 'Inter',
				'typography_font_weight' => '400',
				'typography_line_height' => array(
					'unit' => 'em',
					'size' => 1.65,
				),
			),
			array(
				'_id'                    => 'accent',
				'title'                  => __( 'Mono', 'semzon-setup' ),
				'typography_typography'  => 'custom',
				'typography_font_family' => 'JetBrains Mono',
				'typography_font_weight' => '500',
				'typography_letter_spacing' => array(
					'unit' => 'em',
					'size' => 0.16,
				),
				'typography_text_transform' => 'uppercase',
			),
		);
	}

	/**
	 * Write every setting above into the active Elementor kit.
	 *
	 * @return array{success:bool,message:string}
	 */
	public static function apply() {
		if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Elementor is not active, so its global settings could not be written.', 'semzon-setup' ),
			);
		}

		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit_id || ! get_post( $kit_id ) ) {
			return array(
				'success' => false,
				'message' => __( 'No active Elementor kit was found. Open Elementor once so it creates one, then re-run this step.', 'semzon-setup' ),
			);
		}

		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		// Responsive ranges.
		$breakpoints = self::breakpoints();
		$settings['active_breakpoints'] = array_keys( $breakpoints );
		foreach ( $breakpoints as $key => $value ) {
			$settings[ $key ] = $value;
		}

		// Palette and type.
		$settings['system_colors']      = self::system_colors();
		$settings['custom_colors']      = self::custom_colors();
		$settings['system_typography']  = self::system_typography();

		// Layout: the design system owns width and spacing through its tokens,
		// so Elementor's own content width is set to match rather than fight it.
		$settings['container_width'] = array(
			'unit' => 'px',
			'size' => 1800,
		);
		$settings['space_between_widgets'] = array(
			'unit' => 'px',
			'size' => 0,
		);

		update_post_meta( $kit_id, '_elementor_page_settings', $settings );

		// Elementor caches generated CSS per kit; clear it so the new values
		// are compiled on the next front-end request.
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		return array(
			'success' => true,
			'message' => __( 'Breakpoints, global colours and global fonts written to the Elementor kit.', 'semzon-setup' ),
		);
	}
}
