<?php
/**
 * Site header — utility bar, brand, mega-menu navigation and mobile drawer.
 *
 * Why this is a theme template rather than an Elementor Theme Builder header:
 * the mega panels carry the data-mega attributes and the .mega / .mega-in /
 * .mega-feat structure that the design system's CSS and the hover-intent
 * JavaScript both bind to. Elementor's Nav Menu and Mega Menu widgets emit
 * their own markup and cannot produce that structure, so building it there
 * would mean redesigning the navigation — which the brief rules out.
 *
 * It is still content-managed: the panels are generated from the Product
 * Category taxonomy and the Solution post type, so adding a machine in
 * wp-admin puts it in the menu automatically. Nothing here is hardcoded copy.
 *
 * @package semzon
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'semzon' ); ?></a>

<div class="util">
	<div class="container container--wide">
		<div class="util-l">
			<?php
			$phone = semzon_option( 'phone', '+92 323 8845411' );
			$email = semzon_option( 'email', 'semzoneng@gmail.com' );
			?>
			<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
			<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
		</div>
		<span class="mono"><?php echo esc_html( semzon_option( 'util_note', 'ISO 9001:2015 · MANGA RAIWIND ROAD, LAHORE' ) ); ?></span>
	</div>
</div>

<header class="header" id="header">
	<div class="container container--wide">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'SEMZON Engineering — home', 'semzon' ); ?>">
			<?php semzon_logo( 'semzon-logo.png', 168, 46 ); ?>
		</a>

		<nav class="nav" aria-label="<?php esc_attr_e( 'Primary', 'semzon' ); ?>">
			<ul>
				<li>
					<button class="nav-t" data-mega="mega-products" aria-expanded="false" aria-controls="mega-products">
						<?php esc_html_e( 'Products', 'semzon' ); ?>
						<?php semzon_chevron(); ?>
					</button>
				</li>
				<li>
					<button class="nav-t" data-mega="mega-solutions" aria-expanded="false" aria-controls="mega-solutions">
						<?php esc_html_e( 'Solutions', 'semzon' ); ?>
						<?php semzon_chevron(); ?>
					</button>
				</li>
				<?php semzon_simple_nav_items(); ?>
			</ul>
		</nav>

		<a class="btn btn--primary btn--sm header-cta" href="<?php echo esc_url( semzon_page_url( 'contact' ) ); ?>">
			<?php esc_html_e( 'Start your project', 'semzon' ); ?>
		</a>

		<button class="burger" aria-expanded="false" aria-controls="drawer" aria-label="<?php esc_attr_e( 'Open menu', 'semzon' ); ?>"><span></span></button>
	</div>

	<?php semzon_mega_products(); ?>
	<?php semzon_mega_solutions(); ?>
</header>

<?php semzon_drawer(); ?>

<main id="main">
