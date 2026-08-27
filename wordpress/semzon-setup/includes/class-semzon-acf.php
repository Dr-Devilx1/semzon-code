<?php
/**
 * ACF field groups for the SEMZON catalogue.
 *
 * Registered in PHP rather than shipped as JSON to import: the field
 * definitions are part of the build, so they belong in version control and
 * should not be editable into drift by accident. Field keys are stable and
 * human-readable so Elementor's dynamic tags keep resolving after an update.
 *
 * @package semzon-setup
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declares every custom field the templates bind to.
 */
class Semzon_ACF {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'acf/init', array( __CLASS__, 'register_groups' ) );
		add_action( 'acf/init', array( __CLASS__, 'register_options_pages' ) );
	}

	/**
	 * Options pages for content that is site-wide rather than per-post.
	 */
	public static function register_options_pages() {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => __( 'Plant Flow', 'semzon-setup' ),
				'menu_title' => __( 'Plant Flow', 'semzon-setup' ),
				'menu_slug'  => 'semzon-plant-flow',
				'parent_slug' => 'semzon-setup',
				'capability' => 'edit_theme_options',
				'redirect'   => false,
			)
		);
	}

	/**
	 * Register all field groups.
	 */
	public static function register_groups() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		self::product_group();
		self::solution_group();
		self::project_group();
		self::plant_flow_group();
	}

	/**
	 * Shared hero fields — every catalogue type opens the same way.
	 *
	 * @param string $prefix Field key prefix.
	 * @return array
	 */
	private static function hero_fields( $prefix ) {
		return array(
			array(
				'key'          => "field_{$prefix}_eyebrow",
				'label'        => __( 'Eyebrow', 'semzon-setup' ),
				'name'         => 'eyebrow',
				'type'         => 'text',
				'instructions' => __( 'Small mono label above the title, e.g. "Products · Grinding" or "Case study · Afghanistan".', 'semzon-setup' ),
			),
			array(
				'key'          => "field_{$prefix}_hero_lead",
				'label'        => __( 'Hero lead', 'semzon-setup' ),
				'name'         => 'hero_lead',
				'type'         => 'textarea',
				'rows'         => 3,
				'new_lines'    => '',
				'instructions' => __( 'The one or two sentence summary directly under the page title.', 'semzon-setup' ),
			),
			array(
				'key'          => "field_{$prefix}_hero_tags",
				'label'        => __( 'Hero tags', 'semzon-setup' ),
				'name'         => 'hero_tags',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => __( 'Add tag', 'semzon-setup' ),
				'instructions' => __( 'The pill badges under the lead, e.g. "2–35 t/h", "Turnkey".', 'semzon-setup' ),
				'sub_fields'   => array(
					array(
						'key'   => "field_{$prefix}_hero_tag_text",
						'label' => __( 'Tag', 'semzon-setup' ),
						'name'  => 'tag_text',
						'type'  => 'text',
					),
				),
			),
		);
	}

	/**
	 * Product fields.
	 */
	private static function product_group() {
		acf_add_local_field_group(
			array(
				'key'      => 'group_semzon_product',
				'title'    => __( 'Product details', 'semzon-setup' ),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'product',
						),
					),
				),
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
				'show_in_rest'          => true,
				'fields'                => array_merge(
					self::hero_fields( 'product' ),
					array(
						array(
							'key'          => 'field_product_body_intro',
							'label'        => __( 'Body introduction', 'semzon-setup' ),
							'name'         => 'body_intro',
							'type'         => 'wysiwyg',
							'tabs'         => 'visual',
							'toolbar'      => 'basic',
							'media_upload' => 0,
							'instructions' => __( 'The prose column beside the machine image.', 'semzon-setup' ),
						),
						array(
							'key'           => 'field_product_why_heading',
							'label'         => __( 'Feature list heading', 'semzon-setup' ),
							'name'          => 'why_heading',
							'type'          => 'text',
							'default_value' => __( 'Why plants specify it', 'semzon-setup' ),
						),
						array(
							'key'          => 'field_product_features',
							'label'        => __( 'Feature list', 'semzon-setup' ),
							'name'         => 'features',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add feature', 'semzon-setup' ),
							'sub_fields'   => array(
								array(
									'key'   => 'field_product_feature_text',
									'label' => __( 'Feature', 'semzon-setup' ),
									'name'  => 'feature_text',
									'type'  => 'text',
								),
							),
						),
						array(
							'key'           => 'field_product_hero_image',
							'label'         => __( 'Machine image', 'semzon-setup' ),
							'name'          => 'hero_image',
							'type'          => 'image',
							'return_format' => 'id',
							'preview_size'  => 'medium',
							'instructions'  => __( 'Render (4:3, 1200×900) or transparent cut-out (1:1, 1000×1000). The CSS frame is fixed, so any image of the right ratio drops in without breaking the layout.', 'semzon-setup' ),
						),
						array(
							'key'           => 'field_product_image_treatment',
							'label'         => __( 'Image treatment', 'semzon-setup' ),
							'name'          => 'image_treatment',
							'type'          => 'select',
							'choices'       => array(
								'render' => __( 'Render — multiply blend, contained', 'semzon-setup' ),
								'cut'    => __( 'Cut-out — drop shadow', 'semzon-setup' ),
								'photo'  => __( 'Photograph — fills the frame', 'semzon-setup' ),
							),
							'default_value' => 'render',
							'instructions'  => __( 'Drives which CSS class the image gets, matching the three treatments in the original build.', 'semzon-setup' ),
						),
						array(
							'key'          => 'field_product_spec_table',
							'label'        => __( 'Spec table', 'semzon-setup' ),
							'name'         => 'spec_table',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add spec row', 'semzon-setup' ),
							'sub_fields'   => array(
								array(
									'key'       => 'field_product_spec_label',
									'label'     => __( 'Label', 'semzon-setup' ),
									'name'      => 'label',
									'type'      => 'text',
									'wrapper'   => array( 'width' => '40' ),
								),
								array(
									'key'     => 'field_product_spec_value',
									'label'   => __( 'Value', 'semzon-setup' ),
									'name'    => 'value',
									'type'    => 'text',
									'wrapper' => array( 'width' => '60' ),
								),
							),
						),
						array(
							'key'           => 'field_product_related',
							'label'         => __( 'Related machines', 'semzon-setup' ),
							'name'          => 'related_products',
							'type'          => 'relationship',
							'post_type'     => array( 'product' ),
							'filters'       => array( 'search', 'taxonomy' ),
							'max'           => 3,
							'return_format' => 'id',
							'instructions'  => __( 'Up to three machines shown in the "Works with" row.', 'semzon-setup' ),
						),
					)
				),
			)
		);
	}

	/**
	 * Solution fields.
	 */
	private static function solution_group() {
		acf_add_local_field_group(
			array(
				'key'      => 'group_semzon_solution',
				'title'    => __( 'Solution details', 'semzon-setup' ),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'solution',
						),
					),
				),
				'position'              => 'normal',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
				'show_in_rest'          => true,
				'fields'                => array_merge(
					self::hero_fields( 'solution' ),
					array(
						array(
							'key'          => 'field_solution_body_intro',
							'label'        => __( 'Body introduction', 'semzon-setup' ),
							'name'         => 'body_intro',
							'type'         => 'wysiwyg',
							'tabs'         => 'visual',
							'toolbar'      => 'basic',
							'media_upload' => 0,
						),
						array(
							'key'           => 'field_solution_secondary_heading',
							'label'         => __( 'Secondary heading', 'semzon-setup' ),
							'name'          => 'secondary_heading',
							'type'          => 'text',
							'default_value' => __( 'Built into every line', 'semzon-setup' ),
						),
						array(
							'key'   => 'field_solution_secondary_text',
							'label' => __( 'Secondary paragraph', 'semzon-setup' ),
							'name'  => 'secondary_text',
							'type'  => 'textarea',
							'rows'  => 3,
							'new_lines' => '',
						),
						array(
							'key'           => 'field_solution_hero_image',
							'label'         => __( 'Line image', 'semzon-setup' ),
							'name'          => 'hero_image',
							'type'          => 'image',
							'return_format' => 'id',
							'preview_size'  => 'medium',
						),
						array(
							'key'           => 'field_solution_image_treatment',
							'label'         => __( 'Image treatment', 'semzon-setup' ),
							'name'          => 'image_treatment',
							'type'          => 'select',
							'choices'       => array(
								'render' => __( 'Render — multiply blend, contained', 'semzon-setup' ),
								'photo'  => __( 'Photograph — fills the frame', 'semzon-setup' ),
							),
							'default_value' => 'photo',
						),
						array(
							'key'          => 'field_solution_flow_intro',
							'label'        => __( 'Flow section intro', 'semzon-setup' ),
							'name'         => 'flow_intro',
							'type'         => 'textarea',
							'rows'         => 2,
							'new_lines'    => '',
							'instructions' => __( 'Optional sentence above the six-stage machine row.', 'semzon-setup' ),
						),
						array(
							'key'          => 'field_solution_machine_flow',
							'label'        => __( 'Machine flow', 'semzon-setup' ),
							'name'         => 'machine_flow',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __( 'Add stage', 'semzon-setup' ),
							'instructions' => __( 'The stages of this production line, in order.', 'semzon-setup' ),
							'sub_fields'   => array(
								array(
									'key'          => 'field_solution_flow_step',
									'label'        => __( 'Step label', 'semzon-setup' ),
									'name'         => 'step_label',
									'type'         => 'text',
									'instructions' => __( 'e.g. "01 — Intake &amp; cleaning"', 'semzon-setup' ),
									'wrapper'      => array( 'width' => '40' ),
								),
								array(
									'key'          => 'field_solution_flow_machine',
									'label'        => __( 'Machine caption', 'semzon-setup' ),
									'name'         => 'machine_name',
									'type'         => 'text',
									'instructions' => __( 'The mono caption, e.g. "HAMMER MILL".', 'semzon-setup' ),
									'wrapper'      => array( 'width' => '30' ),
								),
								array(
									'key'           => 'field_solution_flow_link',
									'label'         => __( 'Linked product', 'semzon-setup' ),
									'name'          => 'linked_product',
									'type'          => 'post_object',
									'post_type'     => array( 'product' ),
									'return_format' => 'id',
									'allow_null'    => 1,
									'wrapper'       => array( 'width' => '30' ),
								),
							),
						),
					)
				),
			)
		);
	}

	/**
	 * Project (case study) fields.
	 */
	private static function project_group() {
		acf_add_local_field_group(
			array(
				'key'      => 'group_semzon_project',
				'title'    => __( 'Project details', 'semzon-setup' ),
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'project',
						),
					),
				),
				'position'              => 'normal',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'active'                => true,
				'show_in_rest'          => true,
				'fields'                => array_merge(
					self::hero_fields( 'project' ),
					array(
						array(
							'key'          => 'field_project_body_intro',
							'label'        => __( 'Case study body', 'semzon-setup' ),
							'name'         => 'body_intro',
							'type'         => 'wysiwyg',
							'tabs'         => 'visual',
							'toolbar'      => 'basic',
							'media_upload' => 0,
						),
						array(
							'key'           => 'field_project_scope_heading',
							'label'         => __( 'Scope list heading', 'semzon-setup' ),
							'name'          => 'scope_heading',
							'type'          => 'text',
							'default_value' => __( 'Scope delivered', 'semzon-setup' ),
						),
						array(
							'key'          => 'field_project_scope_list',
							'label'        => __( 'Scope delivered', 'semzon-setup' ),
							'name'         => 'scope_list',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add item', 'semzon-setup' ),
							'sub_fields'   => array(
								array(
									'key'   => 'field_project_scope_item',
									'label' => __( 'Item', 'semzon-setup' ),
									'name'  => 'item_text',
									'type'  => 'text',
								),
							),
						),
						array(
							'key'           => 'field_project_gallery',
							'label'         => __( 'Project gallery', 'semzon-setup' ),
							'name'          => 'gallery',
							'type'          => 'gallery',
							'return_format' => 'id',
							'preview_size'  => 'medium',
							'instructions'  => __( 'Site photographs, 16:10 (1600×1000).', 'semzon-setup' ),
						),
						array(
							'key'          => 'field_project_facts',
							'label'        => __( 'Project facts', 'semzon-setup' ),
							'name'         => 'project_facts',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add fact', 'semzon-setup' ),
							'instructions' => __( 'Optional mono facts strip — capacity, location, year.', 'semzon-setup' ),
							'sub_fields'   => array(
								array(
									'key'     => 'field_project_fact_label',
									'label'   => __( 'Label', 'semzon-setup' ),
									'name'    => 'label',
									'type'    => 'text',
									'wrapper' => array( 'width' => '40' ),
								),
								array(
									'key'     => 'field_project_fact_value',
									'label'   => __( 'Value', 'semzon-setup' ),
									'name'    => 'value',
									'type'    => 'text',
									'wrapper' => array( 'width' => '60' ),
								),
							),
						),
					)
				),
			)
		);
	}

	/**
	 * Plant flow — the homepage's interactive six-stage rail.
	 *
	 * Lives on an options page because it describes the company's process, not
	 * any single post. The motion layer reads it through wp_localize_script.
	 */
	private static function plant_flow_group() {
		acf_add_local_field_group(
			array(
				'key'      => 'group_semzon_plant_flow',
				'title'    => __( 'Plant flow stages', 'semzon-setup' ),
				'location' => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'semzon-plant-flow',
						),
					),
				),
				'active' => true,
				'fields' => array(
					array(
						'key'          => 'field_flow_stages',
						'label'        => __( 'Stages', 'semzon-setup' ),
						'name'         => 'plant_flow_stages',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add stage', 'semzon-setup' ),
						'instructions' => __( 'Drives the interactive plant-flow section on the homepage. Six stages is the designed length; the rail adapts if you add or remove one.', 'semzon-setup' ),
						'sub_fields'   => array(
							array(
								'key'     => 'field_flow_number',
								'label'   => __( 'Number', 'semzon-setup' ),
								'name'    => 'stage_number',
								'type'    => 'text',
								'wrapper' => array( 'width' => '15' ),
							),
							array(
								'key'     => 'field_flow_title',
								'label'   => __( 'Title', 'semzon-setup' ),
								'name'    => 'stage_title',
								'type'    => 'text',
								'wrapper' => array( 'width' => '85' ),
							),
							array(
								'key'       => 'field_flow_description',
								'label'     => __( 'Description', 'semzon-setup' ),
								'name'      => 'stage_description',
								'type'      => 'textarea',
								'rows'      => 3,
								'new_lines' => '',
							),
							array(
								'key'          => 'field_flow_machines',
								'label'        => __( 'Machines at this stage', 'semzon-setup' ),
								'name'         => 'machines',
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( 'Add machine', 'semzon-setup' ),
								'sub_fields'   => array(
									array(
										'key'     => 'field_flow_machine_name',
										'label'   => __( 'Name', 'semzon-setup' ),
										'name'    => 'machine_name',
										'type'    => 'text',
										'wrapper' => array( 'width' => '35' ),
									),
									array(
										'key'     => 'field_flow_machine_spec',
										'label'   => __( 'Spec caption', 'semzon-setup' ),
										'name'    => 'machine_spec',
										'type'    => 'text',
										'wrapper' => array( 'width' => '35' ),
									),
									array(
										'key'           => 'field_flow_machine_link',
										'label'         => __( 'Product', 'semzon-setup' ),
										'name'          => 'linked_product',
										'type'          => 'post_object',
										'post_type'     => array( 'product' ),
										'return_format' => 'object',
										'allow_null'    => 1,
										'wrapper'       => array( 'width' => '30' ),
									),
								),
							),
						),
					),
				),
			)
		);
	}
}
