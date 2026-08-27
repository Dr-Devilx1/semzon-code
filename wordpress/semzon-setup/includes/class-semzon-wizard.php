<?php
/**
 * Setup wizard — the installer UI.
 *
 * Each step runs over AJAX and reports its own result, so a failure names the
 * step that failed instead of leaving a half-configured site. Every step is
 * safe to re-run.
 *
 * @package semzon-setup
 */

defined( 'ABSPATH' ) || exit;

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
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'wp_ajax_semzon_wizard_step', array( __CLASS__, 'ajax_step' ) );
	}

	/**
	 * Admin menu entries.
	 */
	public static function menu() {
		add_menu_page(
			__( 'SEMZON', 'semzon-setup' ),
			__( 'SEMZON', 'semzon-setup' ),
			self::CAPABILITY,
			'semzon-setup',
			array( __CLASS__, 'render' ),
			'dashicons-admin-generic',
			58
		);

		add_submenu_page(
			'semzon-setup',
			__( 'Setup Wizard', 'semzon-setup' ),
			__( 'Setup Wizard', 'semzon-setup' ),
			self::CAPABILITY,
			'semzon-setup',
			array( __CLASS__, 'render' )
		);

		add_submenu_page(
			'semzon-setup',
			__( 'Settings', 'semzon-setup' ),
			__( 'Settings', 'semzon-setup' ),
			self::CAPABILITY,
			'semzon-settings',
			array( 'Semzon_Settings', 'render_page' )
		);
	}

	/**
	 * The ordered list of steps the wizard runs.
	 *
	 * @return array<string,string> step key => human label
	 */
	public static function steps() {
		return array(
			'requirements' => __( 'Check requirements', 'semzon-setup' ),
			'post_types'   => __( 'Register post types & taxonomies', 'semzon-setup' ),
			'elementor'    => __( 'Configure Elementor breakpoints, colours & fonts', 'semzon-setup' ),
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
				if ( ! did_action( 'elementor/loaded' ) ) {
					$missing[] = 'Elementor';
				}
				if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
					$missing[] = 'Elementor Pro';
				}
				if ( ! function_exists( 'acf_add_local_field_group' ) ) {
					$missing[] = 'ACF PRO';
				}
				if ( 'semzon-child' !== get_stylesheet() && ! wp_get_theme()->get( 'Name' ) ) {
					$missing[] = __( 'SEMZON child theme', 'semzon-setup' );
				}

				if ( $missing ) {
					return array(
						'success' => false,
						'message' => sprintf(
							/* translators: %s: comma separated list */
							__( 'Missing: %s. Activate these, then re-run.', 'semzon-setup' ),
							implode( ', ', $missing )
						),
					);
				}
				return array(
					'success' => true,
					'message' => __( 'Elementor Pro, ACF PRO and the theme are active.', 'semzon-setup' ),
				);

			case 'post_types':
				Semzon_CPT::register();
				return array(
					'success' => true,
					'message' => __( 'Product, Solution and Project registered, with Product Category and Industry taxonomies.', 'semzon-setup' ),
				);

			case 'elementor':
				return Semzon_Elementor::apply();

			case 'pages':
				$r = Semzon_Importer::create_pages();
				return array(
					'success' => true,
					'message' => sprintf(
						/* translators: 1: created count, 2: existing count */
						__( '%1$d pages created, %2$d already existed. Home set as the front page.', 'semzon-setup' ),
						$r['created'],
						$r['existing']
					),
				);

			case 'products':
				$r = Semzon_Importer::import_products();
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

			case 'solutions':
				$r = Semzon_Importer::import_solutions();
				return array(
					'success' => true,
					'message' => sprintf(
						/* translators: 1: created, 2: updated, 3: images */
						__( '%1$d created, %2$d updated, %3$d images added.', 'semzon-setup' ),
						$r['created'],
						$r['updated'],
						$r['images']
					),
				);

			case 'projects':
				$r = Semzon_Importer::import_projects();
				return array(
					'success' => true,
					'message' => sprintf(
						/* translators: 1: created, 2: updated, 3: images */
						__( '%1$d created, %2$d updated, %3$d gallery images added.', 'semzon-setup' ),
						$r['created'],
						$r['updated'],
						$r['images']
					),
				);

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
				Semzon_CPT::register();
				flush_rewrite_rules();

				$state = (array) get_option( 'semzon_setup_state', array() );
				$state['finished_at'] = current_time( 'mysql' );
				update_option( 'semzon_setup_state', $state );

				return array(
					'success' => true,
					'message' => __( 'Permalinks flushed. Setup complete.', 'semzon-setup' ),
				);
		}

		return array(
			'success' => false,
			'message' => __( 'Unknown step.', 'semzon-setup' ),
		);
	}

	/**
	 * AJAX endpoint for a single step.
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
							esc_html__( 'Setup last completed %s. Re-running is safe — every step updates in place instead of duplicating content.', 'semzon-setup' ),
							esc_html( $state['finished_at'] )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<p style="max-width:70ch">
				<?php esc_html_e( 'This runs the one-time configuration: post types, custom fields, Elementor global settings and the catalogue content exported from the original site. Once it finishes, the site is a normal WordPress + Elementor + ACF install — nothing here renders the front end.', 'semzon-setup' ); ?>
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
			</p>

			<style>
				.semzon-steps { margin: 20px 0; padding: 0; list-style: none; max-width: 80ch; }
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
				var btn = document.getElementById( 'semzon-run' );
				var items = Array.prototype.slice.call(
					document.querySelectorAll( '#semzon-steps li' )
				);
				var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
				var nonce = <?php echo wp_json_encode( wp_create_nonce( self::NONCE ) ); ?>;

				function setState( li, state, msg, mark ) {
					li.classList.remove( 'running', 'done', 'failed' );
					if ( state ) { li.classList.add( state ); }
					li.querySelector( '.semzon-status' ).textContent = mark;
					li.querySelector( '.semzon-msg' ).textContent = msg || '';
				}

				function runStep( i ) {
					if ( i >= items.length ) {
						btn.disabled = false;
						btn.textContent = <?php echo wp_json_encode( __( 'Run setup again', 'semzon-setup' ) ); ?>;
						return;
					}

					var li = items[ i ];
					setState( li, 'running', <?php echo wp_json_encode( __( 'Working…', 'semzon-setup' ) ); ?>, '◌' );

					var body = new URLSearchParams();
					body.append( 'action', 'semzon_wizard_step' );
					body.append( 'nonce', nonce );
					body.append( 'step', li.getAttribute( 'data-step' ) );

					fetch( ajaxUrl, {
						method: 'POST',
						credentials: 'same-origin',
						body: body
					} )
					.then( function ( r ) { return r.json(); } )
					.then( function ( res ) {
						var payload = ( res && res.data ) || {};
						if ( res && res.success ) {
							setState( li, 'done', payload.message, '✔' );
							runStep( i + 1 );
						} else {
							setState( li, 'failed', payload.message || 'Failed.', '✕' );
							btn.disabled = false;
							btn.textContent = <?php echo wp_json_encode( __( 'Retry', 'semzon-setup' ) ); ?>;
						}
					} )
					.catch( function ( err ) {
						setState( li, 'failed', String( err ), '✕' );
						btn.disabled = false;
						btn.textContent = <?php echo wp_json_encode( __( 'Retry', 'semzon-setup' ) ); ?>;
					} );
				}

				btn.addEventListener( 'click', function () {
					btn.disabled = true;
					items.forEach( function ( li ) { setState( li, '', '', '•' ); } );
					runStep( 0 );
				} );
			}() );
			</script>
		</div>
		<?php
	}
}
