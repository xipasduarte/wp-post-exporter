<?php

namespace xipasduarte\WP\Plugin\PostExporter;

use League\Csv\Writer;

/**
 * The dashboard-specific functionality of the plugin
 *
 * @package PostExporter
 * @since   1.0.0
 */
class Admin {

	/**
	 * The plugin's instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Plugin
	 */
	private $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		\add_action( 'wp_loaded', [ $this, 'export' ] );
		\add_action( 'admin_menu', [ $this, 'register_menu' ] );
	}

	/**
	 * Register the plugin menu page.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_menu() {
		\add_submenu_page(
			'tools.php',
			'WP Post Exporter',
			'WP Post Exporter',
			'manage_options',
			'wp-post-exporter',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Callback to render the contents of the admin sub-menu page.
	 * 
	 * @since 1.1.0 Added post fields to export selection.
	 * @since 1.0.0
	 */
	public function render_page() {
		echo '<div class="wrap">';
		echo '<h1>WP Post Exporter</h1>';

		printf(
			'<p>%s</p>',
			esc_html__( 'The exporter will retrive the database values for the
			selected content, images, videos and other types os media, other 
			than text, will be displayed as a link to their sources or not at 
			all.', 'wp-post-exporter' )
		);

		printf(
			'<form method="post">
				%s',
			\wp_nonce_field( 'wp_post_exporter_export', '_wppe_nonce', true, false )
		);

		// Filter
		printf(
			'<h2 class="title">%s</h2>
			<p>%s</p>',
			\esc_html__( 'Filters', 'wp-post-exporter' ),
			\esc_html__( 'Apply filters to constraint posts that are exported.', 'wp-post-exporter' )
		);

		echo '<table class="form-table">';
		echo '<tbody>';

		$this->render_type_select();
		$this->render_status_select();

		echo '</tbody>';
		echo '</table>';

		// Data selection
		printf(
			'<h2 class="title">%s</h2>
			<p>%s</p>',
			\esc_html__( 'Data to export', 'wp-post-exporter' ),
			\esc_html__( "Select which fields you'd like to export.", 'wp-post-exporter' )
		);

		echo '<table class="form-table">';
		echo '<tbody>';

		$this->render_default_select();
		$this->render_meta_select();

		echo '</tbody>';
		echo '</table>';

		printf(
			'<p><input type="submit" class="button button-primary" value="%s" /></p>',
			\esc_attr__( 'Export', 'wp-post-exporter' )
		);

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Display available post types for selection.
	 * @since 1.0.0
	 */
	public function render_type_select() {
		$types      = \get_post_types( [], 'objects' );
		$post_types = '';

		foreach ( $types as $type ) {
			$post_types .= sprintf(
				'<option value="%s">%s</option>',
				\esc_attr( $type->name ),
				\esc_html( $type->labels->name )
			);
		}

		printf(
			'<tr>
				<th scope="row">%s</th>
				<td>
					<fieldset>
						<select name="post_type[]" placeholder="%s" size="6" multiple="multiple">
							%s
						</select>
					</fieldset>
				</td>
			</tr>',
			\esc_html__( 'Post type', 'wp-post-exporter' ),
			\esc_html__( 'Select a content type', 'wp-post-exporter' ),
			$post_types
		);
	}

	/**
	 * Display available post stati to be selected.
	 * 
	 * Status is not post_type specific and so every registered status is
	 * available for selection.
	 * 
	 * @since 1.0.0
	 */
	public function render_status_select() {
		$stati         = \get_post_stati( [], 'objects' );
		$builtin_stati = [];
		$custom_stati  = [];

		foreach ( $stati as $name => $info ) {
			if ( $info->_builtin ) {
				$builtin_stati[ $name ] = $info->label;
			} else {
				$custom_stati[ $name ] = $info->label;
			}
		}

		printf(
			'<tr>
				<th scope="row">%s</th>
				<td>
					<fieldset>
						<select name="post_status[]" size="6" multiple="multiple">
							<optgroup label="%s">%s</optgroup>
							<optgroup label="%s">%s</optgroup>
						</select>
					</fieldset>
				</td>
			</tr>',
			\esc_html__( 'Post status', 'wp-post-exporter' ),
			\esc_attr__( 'Custom', 'wp-post-exporter' ),
			$this->build_options( $custom_stati ),
			\esc_attr__( 'Builtin', 'wp-post-exporter' ),
			$this->build_options( $builtin_stati )
		);
	}

	/**
	 * Display meta selection to include in export.
	 * 
	 * Meta keys are related to posts when they are saved, there is no way to
	 * determine which meta keys are related to a given post_type without
	 * sampling a post.
	 * 
	 * @since 1.0.0
	 */
	public function render_meta_select() {
		$post_id = \get_posts( [
			'numberposts' => 1,
			'post_type'   => 'registration',
			'post_status' => \get_post_stati(),
			'fields'      => 'ids',
		] );
		$meta = \get_post_meta( array_shift( $post_id ) );

		foreach ( $meta as $key => &$value ) {

			// Avoid private keys.
			if ( strpos( $key, '_' ) === 0 ) {
				unset( $meta[ $key ] );
				continue;
			}

			$meta[ $key ] = $key;
		}

		printf(
			'<tr>
				<th scope="row">%s</th>
				<td>
					<fieldset>
						<select name="post_meta[]" size="6" multiple="multiple">
							%s
						</select>
					</fieldset>
				</td>
			</tr>',
			\esc_html__( 'Post meta', 'wp-post-exporter' ),
			$this->build_options( $meta )
		);
	}

	/**
	 * Display default fields for selection to include in export.
	 * 
	 * @since 1.1.0
	 */
	public function render_default_select() {
		$fields = [
			'post_author'  => \__( 'Author', 'wp-post-exporter' ),
			'post_date'    => \__( 'Creation date', 'wp-post-exporter' ),
			'post_content' => \__( 'Content', 'wp-post-exporter' ),
			'post_title'   => \__( 'Title', 'wp-post-exporter' ),
			'post_excerpt' => \__( 'Excerpt', 'wp-post-exporter' ),
			'post_status'  => \__( 'Status', 'wp-post-exporter' ),
			'post_type'    => \__( 'Type', 'wp-post-exporter' ),
		];

		printf(
			'<tr>
				<th scope="row">%s</th>
				<td>
					<fieldset>
						<select name="post_fields[]" size="6" multiple="multiple">
							%s
						</select>
					</fieldset>
				</td>
			</tr>',
			\esc_html__( 'Post fields', 'wp-post-exporter' ),
			$this->build_options( $fields )
		);
	}

	/**
	 * Export content based selected options.
	 * @since 1.0.0
	 */
	public function export() {

		// Don't export if the conditions aren't met.
		if (
			isset( $_POST['_wppe_nonce'] ) &&
			\check_admin_referer( 'wp_post_exporter_export', '_wppe_nonce' )
		) {
			Export::export();
		}
	}

	/**
	 * Build status options.
	 * @since 1.0.0
	 */
	private function build_options( $options ) {
		$result = '';

		foreach ( $options as $value => $label ) {
			$result .= sprintf(
				'<option value="%s">%s</option>',
				\esc_attr( $value ),
				\esc_html( $label )
			);
		}

		return $result;
	}
}
