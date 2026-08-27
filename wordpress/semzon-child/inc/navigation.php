<?php
/**
 * Navigation rendering — mega panels, drawer and the sticky action bar.
 *
 * The panels are generated from real content (Product Category terms and their
 * Products, Solution posts) rather than a hand-maintained menu, so adding a
 * machine in wp-admin puts it in the navigation with no further work.
 *
 * @package semzon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a SEMZON site option with a fallback.
 *
 * @param string $key     Option suffix.
 * @param string $default Fallback value.
 * @return string
 */
function semzon_option( $key, $default = '' ) {
	$value = get_option( 'semzon_' . $key, '' );
	return '' !== $value && null !== $value ? (string) $value : $default;
}

/**
 * Permalink for one of the wizard-created pages, falling back to home.
 *
 * @param string $slug Page slug.
 * @return string
 */
function semzon_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/**
 * Print the brand logo, preferring the WordPress custom logo when one is set.
 *
 * @param string $fallback Bundled file name under the plugin's brand folder.
 * @param int    $width    Intrinsic width.
 * @param int    $height   Intrinsic height.
 */
function semzon_logo( $fallback, $width, $height ) {
	$custom = get_theme_mod( 'custom_logo' );
	if ( $custom ) {
		echo wp_get_attachment_image(
			$custom,
			'full',
			false,
			array(
				'alt'    => get_bloginfo( 'name' ),
				'width'  => $width,
				'height' => $height,
			)
		);
		return;
	}

	$attachment = get_posts(
		array(
			'post_type'     => 'attachment',
			'post_status'   => 'inherit',
			'numberposts'   => 1,
			'fields'        => 'ids',
			'no_found_rows' => true,
			'meta_query'    => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_wp_attached_file',
					'value'   => '/' . $fallback,
					'compare' => 'LIKE',
				),
			),
		)
	);

	if ( $attachment ) {
		echo wp_get_attachment_image(
			$attachment[0],
			'full',
			false,
			array(
				'alt'    => get_bloginfo( 'name' ),
				'width'  => $width,
				'height' => $height,
			)
		);
		return;
	}

	printf( '<span class="f-word">%s</span>', esc_html( get_bloginfo( 'name' ) ) );
}

/**
 * The small chevron used on mega-menu triggers.
 */
function semzon_chevron() {
	echo '<svg class="cv" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">'
		. '<path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.6"/></svg>';
}

/**
 * The non-mega top-level items.
 *
 * Uses the "primary" menu when one is assigned so an administrator can reorder
 * or rename them; falls back to the original five when no menu exists yet.
 */
function semzon_simple_nav_items() {
	$items = array();

	if ( has_nav_menu( 'primary' ) ) {
		$locations = get_nav_menu_locations();
		$menu      = wp_get_nav_menu_object( $locations['primary'] );
		if ( $menu ) {
			foreach ( wp_get_nav_menu_items( $menu->term_id ) as $item ) {
				if ( (int) $item->menu_item_parent === 0 ) {
					$items[] = array( $item->title, $item->url );
				}
			}
		}
	}

	if ( ! $items ) {
		$items = array(
			array( __( 'Projects', 'semzon' ), get_post_type_archive_link( 'project' ) ),
			array( __( 'Industries', 'semzon' ), semzon_page_url( 'industries' ) ),
			array( __( 'Services', 'semzon' ), semzon_page_url( 'services' ) ),
			array( __( 'About', 'semzon' ), semzon_page_url( 'about' ) ),
		);
	}

	foreach ( $items as $item ) {
		if ( ! $item[1] ) {
			continue;
		}
		printf( '<li><a href="%s">%s</a></li>', esc_url( $item[1] ), esc_html( $item[0] ) );
	}
}

/**
 * Products mega panel — Product Category terms and the machines in each.
 *
 * The original grouped 22 machines into four columns by function. The taxonomy
 * carries that grouping now, so the panel is generated rather than maintained.
 */
function semzon_mega_products() {
	$groups = array(
		__( 'Size reduction & cleaning', 'semzon' ) => array( 'Grinding', 'Cleaning', 'Screening' ),
		__( 'Mixing & liquids', 'semzon' )          => array( 'Mixing' ),
		__( 'Pelleting & extrusion', 'semzon' )     => array( 'Pelleting', 'Extrusion', 'Cooling & Drying' ),
		__( 'Handling, packing & control', 'semzon' ) => array( 'Conveying', 'Packing', 'Automation', 'Fabrication', 'Plant Support' ),
	);
	?>
	<div class="mega" id="mega-products" role="region" aria-label="<?php esc_attr_e( 'Products menu', 'semzon' ); ?>">
		<div class="container container--wide">
			<div class="mega-in">
				<?php foreach ( $groups as $label => $categories ) : ?>
					<?php
					$posts = get_posts(
						array(
							'post_type'      => 'product',
							'posts_per_page' => -1,
							'orderby'        => 'menu_order title',
							'order'          => 'ASC',
							'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
								array(
									'taxonomy' => 'product_category',
									'field'    => 'name',
									'terms'    => $categories,
								),
							),
						)
					);
					if ( ! $posts ) {
						continue;
					}
					?>
					<div>
						<h5><?php echo esc_html( $label ); ?></h5>
						<ul>
							<?php foreach ( $posts as $post ) : ?>
								<li><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>

				<?php semzon_mega_feature( 'product', 'feed-pellet-mill', 'FEED PELLET MILL · SZLH · 2–35 T/H · Ø350–768 MM' ); ?>
			</div>

			<div class="mega-foot">
				<a class="link-arrow" href="<?php echo esc_url( (string) get_post_type_archive_link( 'product' ) ); ?>">
					<?php esc_html_e( 'All products', 'semzon' ); ?> <span class="ar">→</span>
				</a>
				<a class="link-arrow" href="<?php echo esc_url( semzon_page_url( 'downloads' ) ); ?>">
					<?php esc_html_e( 'Download catalogue (PDF)', 'semzon' ); ?> <span class="ar">→</span>
				</a>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Solutions mega panel — the production lines, split feed vs. fertilizer.
 */
function semzon_mega_solutions() {
	$feed_slugs = array( 'poultry-livestock-feed-line', 'aqua-pet-feed-line', 'cattle-feed-line' );

	$all = get_posts(
		array(
			'post_type'      => 'solution',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);

	$feed  = array();
	$other = array();
	foreach ( $all as $post ) {
		if ( in_array( $post->post_name, $feed_slugs, true ) ) {
			$feed[] = $post;
		} else {
			$other[] = $post;
		}
	}
	?>
	<div class="mega" id="mega-solutions" role="region" aria-label="<?php esc_attr_e( 'Solutions menu', 'semzon' ); ?>">
		<div class="container container--wide">
			<div class="mega-in mega-in--solutions">
				<div>
					<h5><?php esc_html_e( 'Feed production lines', 'semzon' ); ?></h5>
					<ul>
						<?php foreach ( $feed as $post ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div>
					<h5><?php esc_html_e( 'Biomass & fertilizer', 'semzon' ); ?></h5>
					<ul>
						<?php foreach ( $other as $post ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<?php semzon_mega_feature( 'project', 'albadar-feed-kabul-20tph', 'ALBADAR FEED · KABUL · 20 T/H TURNKEY', true ); ?>
			</div>

			<div class="mega-foot">
				<a class="link-arrow" href="<?php echo esc_url( (string) get_post_type_archive_link( 'solution' ) ); ?>">
					<?php esc_html_e( 'All production lines', 'semzon' ); ?> <span class="ar">→</span>
				</a>
				<a class="link-arrow" href="<?php echo esc_url( home_url( '/#flow' ) ); ?>">
					<?php esc_html_e( 'See how a plant works', 'semzon' ); ?> <span class="ar">→</span>
				</a>
			</div>
		</div>
	</div>
	<?php
}

/**
 * The feature tile at the right edge of a mega panel.
 *
 * @param string $post_type Post type to pull from.
 * @param string $slug      Post slug to feature.
 * @param string $caption   Mono caption beneath the image.
 * @param bool   $photo     Whether the image is a photograph (cover-fit).
 */
function semzon_mega_feature( $post_type, $slug, $caption, $photo = false ) {
	$posts = get_posts(
		array(
			'name'          => $slug,
			'post_type'     => $post_type,
			'posts_per_page' => 1,
			'no_found_rows' => true,
		)
	);
	if ( ! $posts ) {
		return;
	}

	$post  = $posts[0];
	$thumb = get_post_thumbnail_id( $post );
	if ( ! $thumb ) {
		return;
	}
	?>
	<div class="mega-feat">
		<a class="mf-img" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<?php
			echo wp_get_attachment_image(
				$thumb,
				'semzon-mega',
				false,
				array(
					'class'   => $photo ? 'ph' : '',
					'loading' => 'lazy',
					'alt'     => get_the_title( $post ),
				)
			);
			?>
		</a>
		<p class="mono"><?php echo esc_html( $caption ); ?></p>
	</div>
	<?php
}

/**
 * Mobile drawer — mirrors the desktop navigation as collapsible groups.
 */
function semzon_drawer() {
	?>
	<div class="drawer" id="drawer" aria-hidden="true">
		<div class="drawer-top">
			<?php semzon_logo( 'semzon-logo.png', 146, 40 ); ?>
			<button class="burger" data-close aria-label="<?php esc_attr_e( 'Close menu', 'semzon' ); ?>" aria-expanded="true"><span></span></button>
		</div>

		<nav aria-label="<?php esc_attr_e( 'Mobile', 'semzon' ); ?>">
			<ul>
				<li>
					<button class="dr-t" aria-expanded="false"><?php esc_html_e( 'Products', 'semzon' ); ?> <span aria-hidden="true">＋</span></button>
					<div class="dr-sub">
						<ul>
							<?php
							$terms = get_terms(
								array(
									'taxonomy'   => 'product_category',
									'hide_empty' => true,
								)
							);
							if ( ! is_wp_error( $terms ) ) {
								foreach ( $terms as $term ) {
									printf(
										'<li><a href="%s">%s</a></li>',
										esc_url( (string) get_term_link( $term ) ),
										esc_html( $term->name )
									);
								}
							}
							?>
							<li><a href="<?php echo esc_url( (string) get_post_type_archive_link( 'product' ) ); ?>"><?php esc_html_e( 'All products →', 'semzon' ); ?></a></li>
						</ul>
					</div>
				</li>
				<li>
					<button class="dr-t" aria-expanded="false"><?php esc_html_e( 'Solutions', 'semzon' ); ?> <span aria-hidden="true">＋</span></button>
					<div class="dr-sub">
						<ul>
							<?php
							foreach ( get_posts( array( 'post_type' => 'solution', 'posts_per_page' => -1 ) ) as $post ) {
								printf(
									'<li><a href="%s">%s</a></li>',
									esc_url( get_permalink( $post ) ),
									esc_html( get_the_title( $post ) )
								);
							}
							?>
						</ul>
					</div>
				</li>
				<?php semzon_simple_nav_items(); ?>
			</ul>
		</nav>

		<div class="dr-contact">
			<a class="btn btn--primary" href="<?php echo esc_url( semzon_page_url( 'contact' ) ); ?>">
				<?php esc_html_e( 'Start your project', 'semzon' ); ?> <span class="ar">↗</span>
			</a>
			<a class="btn btn--secondary" href="<?php echo esc_url( semzon_whatsapp_url() ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'WhatsApp an engineer', 'semzon' ); ?>
			</a>
			<p class="small"><?php echo esc_html( semzon_option( 'address', '2.5 KM Manga Raiwind Road, Manga Mandi, Lahore' ) ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * WhatsApp deep link with the site's standard opening message.
 *
 * @return string
 */
function semzon_whatsapp_url() {
	$number = preg_replace( '/\D+/', '', semzon_option( 'whatsapp', '923238845411' ) );
	return 'https://wa.me/' . $number . '?text=' . rawurlencode( "Salaam, I'd like to discuss a plant." );
}

/**
 * Sticky mobile action bar — call, WhatsApp, quote.
 */
function semzon_action_bar() {
	if ( is_admin() ) {
		return;
	}
	$phone = preg_replace( '/\s+/', '', semzon_option( 'phone', '+92 323 8845411' ) );
	?>
	<nav class="action-bar" id="action-bar" aria-label="<?php esc_attr_e( 'Quick contact', 'semzon' ); ?>">
		<a href="tel:<?php echo esc_attr( $phone ); ?>">
			<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a12 12 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.6"/></svg>
			<?php esc_html_e( 'Call', 'semzon' ); ?>
		</a>
		<a href="<?php echo esc_url( semzon_whatsapp_url() ); ?>" target="_blank" rel="noopener">
			<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.7-1.2A9 9 0 1 0 12 3Z" stroke="currentColor" stroke-width="1.6"/><path d="M9 8.5c0 4 2.5 6.5 6.5 6.5l.8-1.8-2-1-1 .7c-1-.5-1.7-1.2-2.2-2.2l.7-1-1-2L9 8.5Z" fill="currentColor"/></svg>
			<?php esc_html_e( 'WhatsApp', 'semzon' ); ?>
		</a>
		<a class="q" href="<?php echo esc_url( semzon_page_url( 'contact' ) ); ?>">
			<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 3h7l4 4v14H7z" stroke="currentColor" stroke-width="1.6"/><path d="M14 3v4h4M10 12h5M10 16h5" stroke="currentColor" stroke-width="1.6"/></svg>
			<?php esc_html_e( 'Get quote', 'semzon' ); ?>
		</a>
	</nav>
	<?php
}
add_action( 'wp_footer', 'semzon_action_bar', 6 );
