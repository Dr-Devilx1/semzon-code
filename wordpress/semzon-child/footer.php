<?php
/**
 * Site footer.
 *
 * Closes the main landmark opened in header.php, then hands off to the
 * Elementor Theme Builder footer if one is assigned. Unlike the header, the
 * footer has no bespoke JavaScript bound to it, so it is a good candidate for
 * a Theme Builder template — import footer.json and it takes over here.
 *
 * The static fallback below only renders when no Elementor footer is set, so
 * the site is never left without contact details mid-build.
 *
 * @package semzon
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php
$has_elementor_footer = function_exists( 'elementor_theme_do_location' )
	&& elementor_theme_do_location( 'footer' );

if ( ! $has_elementor_footer ) :
	?>
	<footer class="footer">
		<div class="f-mark" aria-hidden="true"><?php echo esc_html( semzon_option( 'wordmark', 'SEMZON' ) ); ?></div>
		<div class="container container--wide">
			<div class="footer-grid">
				<div class="f-brand">
					<?php semzon_logo( 'semzon-logo-white.png', 210, 57 ); ?>
					<p style="max-width:34ch"><?php echo esc_html( semzon_option( 'footer_blurb', 'Custom industrial machinery and turnkey feed, biomass and fertilizer plants — engineered in Lahore since 1994.' ) ); ?></p>
					<span class="mono" style="color:var(--brand-400);font-size:.66rem;letter-spacing:.12em">
						<?php echo esc_html( semzon_option( 'motto', 'QUALITY FIRST · SERVICE FIRST · PRICE REASONABLE' ) ); ?>
					</span>
				</div>

				<div>
					<h5><?php esc_html_e( 'Products', 'semzon' ); ?></h5>
					<ul>
						<?php
						foreach ( get_posts( array( 'post_type' => 'product', 'posts_per_page' => 5 ) ) as $post ) {
							printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $post ) ), esc_html( get_the_title( $post ) ) );
						}
						?>
						<li><a href="<?php echo esc_url( (string) get_post_type_archive_link( 'product' ) ); ?>"><?php esc_html_e( 'All products', 'semzon' ); ?></a></li>
					</ul>
				</div>

				<div>
					<h5><?php esc_html_e( 'Solutions', 'semzon' ); ?></h5>
					<ul>
						<?php
						foreach ( get_posts( array( 'post_type' => 'solution', 'posts_per_page' => 5 ) ) as $post ) {
							printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $post ) ), esc_html( get_the_title( $post ) ) );
						}
						?>
						<li><a href="<?php echo esc_url( (string) get_post_type_archive_link( 'project' ) ); ?>"><?php esc_html_e( 'Installed projects', 'semzon' ); ?></a></li>
					</ul>
				</div>

				<div>
					<h5><?php esc_html_e( 'Contact', 'semzon' ); ?></h5>
					<ul class="f-contact">
						<li><b><?php echo esc_html( semzon_option( 'phone', '+92 323 8845411' ) ); ?></b><span class="small"><?php esc_html_e( 'Phone & WhatsApp', 'semzon' ); ?></span></li>
						<li><b><?php echo esc_html( semzon_option( 'email', 'semzoneng@gmail.com' ) ); ?></b><span class="small"><?php esc_html_e( 'Engineering enquiries', 'semzon' ); ?></span></li>
						<li><b><?php echo esc_html( semzon_option( 'address_line', '2.5 KM Manga Raiwind Road' ) ); ?></b><span class="small"><?php echo esc_html( semzon_option( 'address_city', 'Manga Mandi, Lahore, Pakistan' ) ); ?></span></li>
					</ul>
				</div>
			</div>

			<div class="f-bottom">
				<span>
					<?php
					printf(
						/* translators: %s: current year */
						esc_html__( '© %s SEMZON Engineering. All rights reserved.', 'semzon' ),
						esc_html( gmdate( 'Y' ) )
					);
					?>
					· <a href="<?php echo esc_url( semzon_page_url( 'privacy' ) ); ?>"><?php esc_html_e( 'Privacy', 'semzon' ); ?></a>
				</span>
				<span class="mono"><?php echo esc_html( semzon_option( 'footer_note', 'WWW.SEMZONENG.COM · ENGINEERING PRECISION THAT FEEDS THE WORLD' ) ); ?></span>
			</div>
		</div>
	</footer>
	<?php
endif;

wp_footer();
?>
</body>
</html>
