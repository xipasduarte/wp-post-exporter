<?php

namespace xipasduarte\WP\Plugin\PostExporter;

use League\Csv\Writer;

/**
 * Export selection to CSV.
 *
 * @package PostExporter
 * @since   1.0.0
 */
class Export {

	/**
	 * Export to CSV.
	 * 
	 * TODO: Add sorting parameters.
	 * 
	 * @since 1.1.0 Export post fields.
	 * @since 1.0.0
	 */
	public static function export() {
		$query_args = [
			'numberposts'         => -1,
			'fields'              => 'ids',
			'ignore_sticky_posts' => true,
		];
		
		// Add filters.
		if ( ! empty( $_POST['post_type'] ) ) {
			$query_args['post_type'] = $_POST['post_type'];
		}
		
		if ( ! empty( $_POST['post_status'] ) ) {
			$query_args['post_status'] = $_POST['post_status'];
		}

		// Build labels.
		$labels = [ 'ID' ];
		if ( ! empty( $_POST['post_fields'] ) ) {
			$labels = array_merge( $labels, $_POST['post_fields'] );
		}

		if ( ! empty( $_POST['post_meta'] ) ) {
			$labels = array_merge( $labels, $_POST['post_meta'] );
		}

		$posts = \get_posts( $query_args );

		foreach ( $posts as &$post ) {
			$post_id = $post;
			$post    = [ $post_id ];

			if ( ! empty( $_POST['post_fields'] ) ) {
				foreach ( $_POST['post_fields'] as $field ) {
					$post[] = \get_post_field( $field, $post_id, 'raw' );
				}
			}

			if ( ! empty( $_POST['post_meta'] ) ) {
				foreach ( $_POST['post_meta'] as $meta_key ) {
					$post[] = \get_post_meta( $post_id, $meta_key, true );
				}
			}
		}

		$writer = Writer::createFromPath( 'php://temp', 'w+');
		$writer->insertOne( $labels );
		$writer->insertAll( $posts );
		$writer->output('export.csv');
		die();
	}
}
