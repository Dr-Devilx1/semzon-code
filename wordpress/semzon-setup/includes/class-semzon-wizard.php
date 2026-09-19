<?php
/**
 * Setup wizard — the installer UI.
 *
 * Each step runs over AJAX and reports its own result, so a failure names the
 * step that failed instead of leaving a half-configured site. Every step is
 * safe to re-run: content is matched by slug and updated, images are matched by
 * filename before being added.
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
if ( ! class_exists( 'Semzon_Wizard' ) ) :

/**
 * Admin wizard.
 */
class Semzon_Wizard {

	const CAPABILITY = 'manage_options';
	const NONCE      = 'semzon_wizard';

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 11 );
		add_action( 'wp_ajax_semzon_wizard_step', array( __CLASS__, 'ajax_step' ) );
	}

	/**
	 * Attach the wizard under the theme's SEMZON menu.
	 *
	 * If the theme is not active there is no parent menu, so the wizard adds
	 * its own top-level entry purely so the requirements notice is reachable.
	 */
	public static function menu() {
		$parent = class_exists( 'Semzon_Admin' ) ? 'semzon' : null;

		if ( ! $parent ) {
			add_menu_page(
				__( 'SEMZON Setup', 'semzon-setup' ),
				__( 'SEMZON Setup', 'semzon-setup' ),
				self::CAPABILITY,
				'semzon-setup',
				array( __CLASS__, 'render' ),
				'dashicons-admin-generic',
				58
			);
			return;
		}

		add_submenu_page(
			$parent,
			__( 'Setup Wizard', 'semzon-setup' ),
			__( 'Setup Wizard', 'semzon-setup' ),
			self::CAPABILITY,
			'semzon-setup',
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * The ordered list of steps.
	 *
	 * Post types and custom fields are absent on purpose — the theme registers
	 * those, so there is nothing for the installer to do about them beyond
	 * confirming they are present and flushing permalinks at the end.
	 *
	 * @return array<string,string> step key => human label
	 */
	public static function steps() {
		return array(
			'requirements' => __( 'Check requirements', 'semzon-setup' ),
			'elementor'    => __( 'Write breakpoints, colours & fonts into Elementor', 'semzon-setup' ),
			'pages'        => __( 'Create the unique pages', 'semzon-setup' ),
			'products'     => __( 'Import 22 products', 'semzon-setup' ),
			'solutions'    => __( 'Import 8 solutions', 'semzon-setup' ),
			'projects'     => __( 'Import 6 project case studies', 'semzon-setup' ),
			'relations'    => __( 'Link related machines & plant flow', 'semzon-setup' ),
			'permalinks'   => __( 'Flush permalinks', 'semzon-setup' ),
		);
	}

	/**
	 * Run a single step.
	 *
	 * @param string $step Step key.
	 * @return array{success:bool,message:string}
	 */
	public static function run_step( $step ) {
		switch ( $step ) {
			case 'requirements':
				$missing = array();
				if ( ! class_exists( 'Semzon_CPT' ) ) {
					$missing[] = __( 'SEMZON Engineering theme', 'semzon-setup' );
				}
				if ( ! did_action( 'elementor/loaded' ) ) {
					$missing[] = 'Elementor';
				}
				if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
					$missing[] = 'Elementor Pro';
				}
				if ( ! function_exists( 'acf_add_local_field_group' ) ) {
					$missing[] = 'ACF PRO';
				}
				if ( ! function_exists( 'update_field' ) ) {
					$missing[] = __( 'ACF update_field() (is ACF fully loaded?)', 'semzon-setup' );
				}

				if ( $missing ) {
					return array(
						'success' => false,
						'message' => sprintf(
							/* translators: %s: comma separated list */
							__( 'Missing: %s. Activate these, then retry.', 'semzon-setup' ),
							implode( ', ', $missing )
						),
					);
				}

				return array(
					'success' => true,
					'message' => __( 'Theme, Elementor Pro and ACF PRO all present.', 'semzon-setup' ),
				);

			case 'elementor':
				return Semzon_Elementor::apply();

			case 'pages':
				$r = Semzon_Importer::create_pages();
				return array(
					'success' => true,
					'message' => sprintf(
						/* translators: 1: created count, 2: existing count */
						__( '%1$d created, %2$d already existed. Home set as the front page.', 'semzon-setup' ),
						$r['created'],
						$r['existing']
					),
				);

			case 'products':
				$r = Semzon_Importer::import_products();
				return self::report( $r );

			case 'solutions':
				$r = Semzon_Importer::import_solutions();
				return self::report( $r );

			case 'projects':
				$r = Semzon_Importer::import_projects();
				return self::report( $r );

			case 'relations':
				$linked = Semzon_Importer::link_products();
				$stages = Semzon_Importer::import_plant_flow();
				return array(
					'success' => true,
					'message' => sprintf(
						/* translators: 1: products linked, 2: flow stages */
						__( '%1$d products linked to related machines; %2$d plant-flow stages seeded.', 'semzon-setup' ),
						$linked,
						$stages
					),
				);

			case 'permalinks':
				if ( class_exists( 'Semzon_CPT' ) ) {
					Semzon_CPT::register();
				}
				flush_rewrite_rules();
				update_option( 'semzon_setup_state', array( 'finished_at' => current_time( 'mysql' ) ) );

				return array(
					'success' => true,
					'message' => __( 'Permalinks flushed. Setup complete — this plugin can now be deleted.', 'semzon-setup' ),
				);
		}

		return array(
			'success' => false,
			'message' => __( 'Unknown step.', 'semzon-setup' ),
		);
	}

	/**
	 * Format an importer result.
	 *
	 * @param array $r Importer stats.
	 * @return array{success:bool,message:string}
	 */
	private static function report( $r ) {
		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: 1: created, 2: updated, 3: images */
				__( '%1$d created, %2$d updated, %3$d images added to the media library.', 'semzon-setup' ),
				$r['created'],
				$r['updated'],
				$r['images']
			),
		);
	}

	/**
	 * AJAX endpoint for one step.
	 */
	public static function ajax_step() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'semzon-setup' ) ), 403 );
		}

		check_ajax_referer( self::NONCE, 'nonce' );

		$step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		if ( ! array_key_exists( $step, self::steps() ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown step.', 'semzon-setup' ) ), 400 );
		}

		$result = self::run_step( $step );

		if ( empty( $result['success'] ) ) {
			wp_send_json_error( $result );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Render the wizard screen.
	 */
	public static function render() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$state    = (array) get_option( 'semzon_setup_state', array() );
		$finished = ! empty( $state['finished_at'] );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SEMZON Setup Wizard', 'semzon-setup' ); ?></h1>

			<?php if ( $finished ) : ?>
				<div class="notice notice-success inline">
					<p>
						<?php
						printf(
							/* translators: %s: date */
							esc_html__( 'Setup completed %s. You can delete this plugin now — the site will not change. Re-running it is also safe.', 'semzon-setup' ),
							esc_html( (string) $state['finished_at'] )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<p style="max-width:72ch">
				<?php esc_html_e( 'This is a one-time installer. It imports the catalogue content and images and configures Elementor. It does not register the post types or custom fields — the theme does that — so once it finishes you can delete this plugin and nothing on the website changes.', 'semzon-setup' ); ?>
			</p>

			<ol id="semzon-steps" class="semzon-steps">
				<?php foreach ( self::steps() as $key => $label ) : ?>
					<li data-step="<?php echo esc_attr( $key ); ?>">
						<span class="semzon-status" aria-hidden="true">•</span>
						<strong><?php echo esc_html( $label ); ?></strong>
						<span class="semzon-msg"></span>
					</li>
				<?php endforeach; ?>
			</ol>

			<p>
				<button class="button button-primary button-hero" id="semzon-run">
					<?php esc_html_e( 'Run setup', 'semzon-setup' ); ?>
				</button>
				<span id="semzon-done" style="display:none;margin-left:14px;font-weight:600;color:#00a32a">
					<?php esc_html_e( 'All steps complete.', 'semzon-setup' ); ?>
				</span>
			</p>

			<style>
				.semzon-steps { margin: 20px 0; padding: 0; list-style: none; max-width: 84ch; }
				.semzon-steps li {
					padding: 12px 16px; border: 1px solid #dcdcde; border-bottom: 0;
					background: #fff; display: flex; gap: 12px; align-items: baseline;
				}
				.semzon-steps li:last-child { border-bottom: 1px solid #dcdcde; }
				.semzon-steps li.running { background: #f6f7f7; }
				.semzon-steps li.done { border-left: 4px solid #00a32a; }
				.semzon-steps li.failed { border-left: 4px solid #d63638; background: #fcf0f1; }
				.semzon-status { font-size: 16px; line-height: 1; width: 16px; flex: none; }
				.semzon-steps li.done .semzon-status { color: #00a32a; }
				.semzon-steps li.failed .semzon-status { color: #d63638; }
				.semzon-msg { color: #50575e; font-size: 13px; }
			</style>

			<script>
			( function () {
				var btn   = document.getElementById( 'semzon-run' );
				var done  = document.getElementById( 'semzon-done' );
				var items = Array.prototype.slice.call( document.querySelectorAll( '#semzon-steps li' ) );
				var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
				var nonce   = <?php echo wp_json_encode( wp_create_nonce( self::NONCE ) ); ?>;
				var LBL_AGAIN = <?php echo wp_json_encode( __( 'Run setup again', 'semzon-setup' ) ); ?>;
				var LBL_RETRY = <?php echo wp_json_encode( __( 'Retry', 'semzon-setup' ) ); ?>;
				var LBL_WORK  = <?php echo wp_json_encode( __( 'Working…', 'semzon-setup' ) ); ?>;

				function setState( li, state, msg, mark ) {
					li.classList.remove( 'running', 'done', 'failed' );
					if ( state ) { li.classList.add( state ); }
					li.querySelector( '.semzon-status' ).textContent = mark;
					li.querySelector( '.semzon-msg' ).textContent = msg || '';
				}

				function runStep( i ) {
					if ( i >= items.length ) {
						btn.disabled = false;
						btn.textContent = LBL_AGAIN;
						done.style.display = 'inline';
						return;
					}

					var li = items[ i ];
					setState( li, 'running', LBL_WORK, '◌' );

					var body = new URLSearchParams();
					body.append( 'action', 'semzon_wizard_step' );
					body.append( 'nonce', nonce );
					body.append( 'step', li.getAttribute( 'data-step' ) );

					fetch( ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
						.then( function ( r ) { return r.json(); } )
						.then( function ( res ) {
							var payload = ( res && res.data ) || {};
							if ( res && res.success ) {
								setState( li, 'done', payload.message, '✔' );
								runStep( i + 1 );
							} else {
								setState( li, 'failed', payload.message || 'Failed.', '✕' );
								btn.disabled = false;
								btn.textContent = LBL_RETRY;
							}
						} )
						.catch( function ( err ) {
							setState( li, 'failed', String( err ), '✕' );
							btn.disabled = false;
							btn.textContent = LBL_RETRY;
						} );
				}

				btn.addEventListener( 'click', function () {
					btn.disabled = true;
					done.style.display = 'none';
					items.forEach( function ( li ) { setState( li, '', '', '•' ); } );
					runStep( 0 );
				} );
			}() );
			</script>
		</div>
		<?php
	}
}

endif;
