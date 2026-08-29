<?php
/**
 * Post types and taxonomies for the SEMZON catalogue.
 *
 * These live in the theme, not the installer plugin, because they are what the
 * site is built on: if they were registered by a plugin, deleting that plugin
 * would make every product, solution and project vanish from the front end and
 * from wp-admin. Registering them here means the installer can be removed the
 * moment setup finishes with no effect on the site.
 *
 * @package semzon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the three repeated content types and the product taxonomy.
 */
class Semzon_CPT {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirect_legacy_urls' ) );
	}

	/**
	 * Register post types and taxonomies.
	 *
	 * URL note: the original static build nested products two levels deep
	 * (/products/grinding/hammer-mill/). Putting a taxonomy term inside a CPT
	 * permalink requires custom rewrite rules that break as soon as a machine
	 * is recategorised, and every such URL would still need a redirect when it
	 * moved. Flat /products/<slug>/ URLs with permanent redirects from the old
	 * paths keep the ranking signal and stay stable, so that is what we do —
	 * see redirect_legacy_urls().
	 */
	public static function register() {
		register_post_type(
			'product',
			array(
				'labels'        => self::labels( __( 'Product', 'semzon' ), __( 'Products', 'semzon' ) ),
				'public'        => true,
				'has_archive'   => 'products',
				'rewrite'       => array(
					'slug'       => 'products',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-hammer',
				'menu_position' => 20,
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
				'show_in_rest'  => true,
				'rest_base'     => 'products',
			)
		);

		register_post_type(
			'solution',
			array(
				'labels'        => self::labels( __( 'Solution', 'semzon' ), __( 'Solutions', 'semzon' ) ),
				'public'        => true,
				'has_archive'   => 'solutions',
				'rewrite'       => array(
					'slug'       => 'solutions',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-networking',
				'menu_position' => 21,
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
				'show_in_rest'  => true,
				'rest_base'     => 'solutions',
			)
		);

		register_post_type(
			'project',
			array(
				'labels'        => self::labels( __( 'Project', 'semzon' ), __( 'Projects', 'semzon' ) ),
				'public'        => true,
				'has_archive'   => 'projects',
				'rewrite'       => array(
					'slug'       => 'projects',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-location-alt',
				'menu_position' => 22,
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
				'show_in_rest'  => true,
				'rest_base'     => 'projects',
			)
		);

		register_taxonomy(
			'product_category',
			array( 'product' ),
			array(
				'labels'            => self::labels( __( 'Product Category', 'semzon' ), __( 'Product Categories', 'semzon' ) ),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'product-category',
					'with_front' => false,
				),
			)
		);

		register_taxonomy(
			'industry',
			array( 'solution', 'project' ),
			array(
				'labels'            => self::labels( __( 'Industry', 'semzon' ), __( 'Industries', 'semzon' ) ),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'industry',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Build a full label set from a singular/plural pair.
	 *
	 * @param string $singular Singular label.
	 * @param string $plural   Plural label.
	 * @return array
	 */
	private static function labels( $singular, $plural ) {
		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'menu_name'          => $plural,
			'add_new'            => __( 'Add New', 'semzon' ),
			/* translators: %s: singular post type label */
			'add_new_item'       => sprintf( __( 'Add New %s', 'semzon' ), $singular ),
			/* translators: %s: singular post type label */
			'edit_item'          => sprintf( __( 'Edit %s', 'semzon' ), $singular ),
			/* translators: %s: singular post type label */
			'new_item'           => sprintf( __( 'New %s', 'semzon' ), $singular ),
			/* translators: %s: singular post type label */
			'view_item'          => sprintf( __( 'View %s', 'semzon' ), $singular ),
			/* translators: %s: plural post type label */
			'search_items'       => sprintf( __( 'Search %s', 'semzon' ), $plural ),
			/* translators: %s: lowercase plural post type label */
			'not_found'          => sprintf( __( 'No %s found', 'semzon' ), strtolower( $plural ) ),
			/* translators: %s: lowercase plural post type label */
			'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'semzon' ), strtolower( $plural ) ),
			'all_items'          => $plural,
			/* translators: %s: singular label */
			'parent_item_colon'  => sprintf( __( 'Parent %s:', 'semzon' ), $singular ),
		);
	}

	/**
	 * Legacy URL map — the nested paths the static site used.
	 *
	 * Keys are the old request paths, values are the new post slugs. Kept in
	 * code rather than an option so the redirects survive a database restore
	 * and are reviewable in version control.
	 *
	 * @return array<string, array{0:string,1:string}> old path => [post type, slug]
	 */
	public static function legacy_map() {
		return array(
			'products/grinding/hammer-mill'            => array( 'product', 'hammer-mill' ),
			'products/cleaning/maize-pre-cleaner'      => array( 'product', 'maize-pre-cleaner' ),
			'products/cleaning/drum-cleaner'           => array( 'product', 'drum-cleaner' ),
			'products/cleaning/pellet-cleaner'         => array( 'product', 'pellet-cleaner' ),
			'products/screening/vibration-screener'    => array( 'product', 'vibration-screener' ),
			'products/screening/crumbler'              => array( 'product', 'crumbler' ),
			'products/mixing/ribbon-mixer'             => array( 'product', 'ribbon-mixer' ),
			'products/mixing/paddle-mixer'             => array( 'product', 'paddle-mixer' ),
			'products/mixing/oil-adding'               => array( 'product', 'oil-adding' ),
			'products/mixing/liquid-adding'            => array( 'product', 'liquid-adding' ),
			'products/pelleting/feed-pellet-mill'      => array( 'product', 'feed-pellet-mill' ),
			'products/extrusion/fish-feed-extruder'    => array( 'product', 'fish-feed-extruder' ),
			'products/extrusion/dry-extruder'          => array( 'product', 'dry-extruder' ),
			'products/cooling-drying/counterflow-cooler' => array( 'product', 'counterflow-cooler' ),
			'products/cooling-drying/dryer'            => array( 'product', 'dryer' ),
			'products/conveying/bucket-elevator'       => array( 'product', 'bucket-elevator' ),
			'products/conveying/conveyors'             => array( 'product', 'conveyors' ),
			'products/conveying/rotary-distributor'    => array( 'product', 'rotary-distributor' ),
			'products/packing/packing-scales'          => array( 'product', 'packing-scales' ),
			'products/automation/plc-control-panels'   => array( 'product', 'plc-control-panels' ),
			'products/fabrication/storage-tanks'       => array( 'product', 'storage-tanks' ),
			'products/ancillary-equipment'             => array( 'product', 'ancillary-equipment' ),
		);
	}

	/**
	 * Permanently redirect the old nested product URLs to their new homes.
	 *
	 * Runs only on 404s, so it costs nothing on a normal request.
	 */
	public static function redirect_legacy_urls() {
		if ( ! is_404() ) {
			return;
		}

		$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
		if ( ! $path ) {
			return;
		}

		$path = trim( (string) $path, '/' );
		$path = preg_replace( '#/index\.html$#', '', $path );

		$map = self::legacy_map();
		if ( ! isset( $map[ $path ] ) ) {
			return;
		}

		list( $post_type, $slug ) = $map[ $path ];

		$found = get_posts(
			array(
				'name'           => $slug,
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'numberposts'    => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		if ( $found ) {
			wp_safe_redirect( get_permalink( $found[0] ), 301 );
			exit;
		}
	}
}

Semzon_CPT::init();
