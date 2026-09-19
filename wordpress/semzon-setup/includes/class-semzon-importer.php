<?php
/**
 * Seeds the catalogue from the bundled JSON exported from the original site.
 *
 * Every step is idempotent: posts are matched by slug and updated rather than
 * duplicated, images are matched by filename before being sideloaded, and
 * relationships are resolved in a second pass once all posts exist. Running
 * the importer twice produces the same site, not two of it.
 *
 * @package semzon-setup
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
if ( ! class_exists( 'Semzon_Importer' ) ) :

/**
 * Content importer.
 */
class Semzon_Importer {

	/**
	 * Read one of the bundled data files.
	 *
	 * @param string $name File name without extension.
	 * @return array
	 */
	private static function data( $name ) {
		$path = SEMZON_SETUP_DIR . 'data/' . $name . '.json';
		if ( ! file_exists( $path ) ) {
			return array();
		}
		$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$decoded = json_decode( (string) $raw, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Find a post of a given type by slug.
	 *
	 * @param string $slug      Post slug.
	 * @param string $post_type Post type.
	 * @return int Post ID or 0.
	 */
	private static function find( $slug, $post_type ) {
		$found = get_posts(
			array(
				'name'          => $slug,
				'post_type'     => $post_type,
				'post_status'   => array( 'publish', 'draft', 'pending', 'private' ),
				'numberposts'   => 1,
				'fields'        => 'ids',
				'no_found_rows' => true,
			)
		);
		return $found ? (int) $found[0] : 0;
	}

	/**
	 * Get an attachment ID for a bundled image, sideloading it once.
	 *
	 * Looks for an existing attachment with the same filename first so a
	 * re-run — or an image the client already uploaded — is reused rather
	 * than duplicated in the media library.
	 *
	 * @param string $filename Bundled file name, e.g. grinding-hammer-mill-smsp-01.webp.
	 * @param string $alt      Alt text to store.
	 * @param string $subdir   Bundled subdirectory: img or brand.
	 * @return int Attachment ID or 0.
	 */
	public static function attachment( $filename, $alt = '', $subdir = 'img' ) {
		$filename = basename( (string) $filename );
		if ( ! $filename ) {
			return 0;
		}

		$existing = get_posts(
			array(
				'post_type'     => 'attachment',
				'post_status'   => 'inherit',
				'numberposts'   => 1,
				'fields'        => 'ids',
				'no_found_rows' => true,
				'meta_query'    => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_wp_attached_file',
						'value'   => '/' . $filename,
						'compare' => 'LIKE',
					),
				),
			)
		);
		if ( $existing ) {
			return (int) $existing[0];
		}

		$source = SEMZON_SETUP_DIR . 'assets/' . $subdir . '/' . $filename;
		if ( ! file_exists( $source ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$upload = wp_upload_bits( $filename, null, file_get_contents( $source ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! empty( $upload['error'] ) ) {
			return 0;
		}

		$filetype = wp_check_filetype( $upload['file'], null );
		$attach_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file']
		);

		if ( is_wp_error( $attach_id ) || ! $attach_id ) {
			return 0;
		}

		wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $upload['file'] ) );

		if ( $alt ) {
			update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt );
		}

		return (int) $attach_id;
	}

	/**
	 * Turn a flat list of strings into ACF repeater rows.
	 *
	 * @param array  $items    Values.
	 * @param string $sub_name Sub-field name.
	 * @return array
	 */
	private static function rows( $items, $sub_name ) {
		$out = array();
		foreach ( (array) $items as $value ) {
			$out[] = array( $sub_name => $value );
		}
		return $out;
	}

	/**
	 * Create or update a post and return its ID.
	 *
	 * @param array  $item      Item data.
	 * @param string $post_type Post type.
	 * @return int
	 */
	private static function upsert( $item, $post_type ) {
		$existing = self::find( $item['slug'], $post_type );

		$postarr = array(
			'post_type'    => $post_type,
			'post_title'   => $item['title'],
			'post_name'    => $item['slug'],
			'post_status'  => 'publish',
			'post_excerpt' => isset( $item['meta_description'] ) ? $item['meta_description'] : '',
		);

		if ( $existing ) {
			$postarr['ID'] = $existing;
			$id = wp_update_post( $postarr, true );
		} else {
			$id = wp_insert_post( $postarr, true );
		}

		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	/**
	 * Import products — pass one: posts, fields, images, categories.
	 *
	 * @return array{created:int,updated:int,images:int}
	 */
	public static function import_products() {
		$items = self::data( 'products' );
		$stats = array(
			'created' => 0,
			'updated' => 0,
			'images'  => 0,
		);

		foreach ( $items as $item ) {
			$existed = (bool) self::find( $item['slug'], 'product' );
			$id = self::upsert( $item, 'product' );
			if ( ! $id ) {
				continue;
			}
			$existed ? $stats['updated']++ : $stats['created']++;

			if ( ! empty( $item['category'] ) ) {
				wp_set_object_terms( $id, array( $item['category'] ), 'product_category', false );
			}

			$image_id = 0;
			if ( ! empty( $item['image'] ) ) {
				$image_id = self::attachment( $item['image'], $item['image_alt'] );
				if ( $image_id ) {
					$stats['images']++;
					set_post_thumbnail( $id, $image_id );
				}
			}

			if ( function_exists( 'update_field' ) ) {
				update_field( 'eyebrow', $item['eyebrow'], $id );
				update_field( 'hero_lead', $item['hero_lead'], $id );
				update_field( 'hero_tags', self::rows( $item['hero_tags'], 'tag_text' ), $id );
				update_field( 'body_intro', $item['body_intro'], $id );
				update_field( 'why_heading', $item['why_heading'], $id );
				update_field( 'features', self::rows( $item['features'], 'feature_text' ), $id );
				update_field( 'spec_table', $item['spec_table'], $id );
				update_field( 'image_treatment', $item['image_treatment'], $id );
				if ( $image_id ) {
					update_field( 'hero_image', $image_id, $id );
				}
			}
		}

		return $stats;
	}

	/**
	 * Import products — pass two: resolve related-machine relationships.
	 *
	 * Runs after every product exists so no link is dropped for pointing at a
	 * machine that had not been created yet.
	 *
	 * @return int Number of posts linked.
	 */
	public static function link_products() {
		if ( ! function_exists( 'update_field' ) ) {
			return 0;
		}

		$items = self::data( 'products' );
		$linked = 0;

		foreach ( $items as $item ) {
			$id = self::find( $item['slug'], 'product' );
			if ( ! $id || empty( $item['related'] ) ) {
				continue;
			}

			$ids = array();
			foreach ( $item['related'] as $slug ) {
				$related_id = self::find( $slug, 'product' );
				if ( $related_id ) {
					$ids[] = $related_id;
				}
			}

			if ( $ids ) {
				update_field( 'related_products', $ids, $id );
				$linked++;
			}
		}

		return $linked;
	}

	/**
	 * Import solutions, resolving each flow stage to its product.
	 *
	 * @return array{created:int,updated:int,images:int}
	 */
	public static function import_solutions() {
		$items = self::data( 'solutions' );
		$stats = array(
			'created' => 0,
			'updated' => 0,
			'images'  => 0,
		);

		foreach ( $items as $item ) {
			$existed = (bool) self::find( $item['slug'], 'solution' );
			$id = self::upsert( $item, 'solution' );
			if ( ! $id ) {
				continue;
			}
			$existed ? $stats['updated']++ : $stats['created']++;

			$image_id = 0;
			if ( ! empty( $item['image'] ) ) {
				$image_id = self::attachment( $item['image'], $item['title'] );
				if ( $image_id ) {
					$stats['images']++;
					set_post_thumbnail( $id, $image_id );
				}
			}

			if ( ! function_exists( 'update_field' ) ) {
				continue;
			}

			$flow = array();
			foreach ( (array) $item['machine_flow'] as $stage ) {
				$product_id = $stage['product_slug'] ? self::find( $stage['product_slug'], 'product' ) : 0;
				$flow[] = array(
					'step_label'     => $stage['step_label'],
					'machine_name'   => $stage['machine_name'],
					'linked_product' => $product_id ? $product_id : '',
				);
			}

			update_field( 'eyebrow', $item['eyebrow'], $id );
			update_field( 'hero_lead', $item['hero_lead'], $id );
			update_field( 'hero_tags', self::rows( $item['hero_tags'], 'tag_text' ), $id );
			update_field( 'body_intro', $item['body_intro'], $id );
			update_field( 'secondary_heading', $item['secondary_heading'], $id );
			update_field( 'secondary_text', $item['secondary_text'], $id );
			update_field( 'flow_intro', $item['flow_intro'], $id );
			update_field( 'image_treatment', $item['image_treatment'], $id );
			update_field( 'machine_flow', $flow, $id );
			if ( $image_id ) {
				update_field( 'hero_image', $image_id, $id );
			}
		}

		return $stats;
	}

	/**
	 * Import project case studies.
	 *
	 * @return array{created:int,updated:int,images:int}
	 */
	public static function import_projects() {
		$items = self::data( 'projects' );
		$stats = array(
			'created' => 0,
			'updated' => 0,
			'images'  => 0,
		);

		foreach ( $items as $item ) {
			$existed = (bool) self::find( $item['slug'], 'project' );
			$id = self::upsert( $item, 'project' );
			if ( ! $id ) {
				continue;
			}
			$existed ? $stats['updated']++ : $stats['created']++;

			$gallery = array();
			foreach ( (array) $item['gallery'] as $filename ) {
				$att = self::attachment( $filename, $item['title'] );
				if ( $att ) {
					$gallery[] = $att;
					$stats['images']++;
				}
			}

			if ( $gallery ) {
				set_post_thumbnail( $id, $gallery[0] );
			}

			if ( ! function_exists( 'update_field' ) ) {
				continue;
			}

			update_field( 'eyebrow', $item['eyebrow'], $id );
			update_field( 'hero_lead', $item['hero_lead'], $id );
			update_field( 'hero_tags', self::rows( $item['hero_tags'], 'tag_text' ), $id );
			update_field( 'body_intro', $item['body_intro'], $id );
			update_field( 'scope_heading', $item['scope_heading'], $id );
			update_field( 'scope_list', self::rows( $item['scope_list'], 'item_text' ), $id );
			if ( $gallery ) {
				update_field( 'gallery', $gallery, $id );
			}
		}

		return $stats;
	}

	/**
	 * Seed the plant-flow options page and the map pin list.
	 *
	 * @return int Number of stages written.
	 */
	public static function import_plant_flow() {
		$stages = self::data( 'plant-flow' );
		$pins   = self::data( 'map-locations' );

		update_option( 'semzon_map_locations', $pins );

		if ( ! function_exists( 'update_field' ) || ! $stages ) {
			return 0;
		}

		$rows = array();
		foreach ( $stages as $stage ) {
			$machines = array();
			foreach ( (array) $stage['machines'] as $machine ) {
				$product_id = $machine['product_slug'] ? self::find( $machine['product_slug'], 'product' ) : 0;
				$machines[] = array(
					'machine_name'   => $machine['machine_name'],
					'machine_spec'   => $machine['machine_spec'],
					'linked_product' => $product_id ? $product_id : '',
				);
			}

			$rows[] = array(
				'stage_number'      => $stage['stage_number'],
				'stage_title'       => $stage['stage_title'],
				'stage_description' => $stage['stage_description'],
				'machines'          => $machines,
			);
		}

		update_field( 'plant_flow_stages', $rows, 'option' );

		return count( $rows );
	}

	/**
	 * Create the pages that are genuinely unique — the ones Elementor will
	 * build by hand rather than through a dynamic template.
	 *
	 * @return array{created:int,existing:int}
	 */
	public static function create_pages() {
		$pages = array(
			'home'       => __( 'Home', 'semzon-setup' ),
			'about'      => __( 'About', 'semzon-setup' ),
			'contact'    => __( 'Contact', 'semzon-setup' ),
			'industries' => __( 'Industries', 'semzon-setup' ),
			'services'   => __( 'Services', 'semzon-setup' ),
			'downloads'  => __( 'Downloads', 'semzon-setup' ),
			'privacy'    => __( 'Privacy Policy', 'semzon-setup' ),
		);

		$stats = array(
			'created'  => 0,
			'existing' => 0,
		);
		$home_id = 0;

		foreach ( $pages as $slug => $title ) {
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				$stats['existing']++;
				if ( 'home' === $slug ) {
					$home_id = $existing->ID;
				}
				continue;
			}

			$id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_title'  => $title,
					'post_name'   => $slug,
					'post_status' => 'publish',
				),
				true
			);

			if ( ! is_wp_error( $id ) ) {
				$stats['created']++;
				if ( 'home' === $slug ) {
					$home_id = (int) $id;
				}
			}
		}

		if ( $home_id ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_id );
		}

		return $stats;
	}
}

endif;
