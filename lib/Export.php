<?php

namespace xipasduarte\WP\Plugin\PostExporter;

use League\Csv\Writer;

/**
 * Export selection to CSV.
 *
 * @package [vendor_name]
 * @since   [initial_version]
 */
class Export {

	/**
	 * Export to CSV.
	 *
	 * @since [initial_version]
	 */
	public static function export() {

		$query_args = [
			'numberposts' => -1,
			'post_type'   => $_POST['post_type'],
			'post_status' => $_POST['post_status'],
			'fields'      => 'ids',
		];

		$posts = \get_posts( $query_args );

		foreach ( $posts as &$post ) {
			$post_id = $post;
			$post    = [ $post_id ];

			foreach ( $_POST['post_meta'] as $meta_key ) {
				$post[] = \get_post_meta( $post_id, $meta_key, true );
			}
		}

		$writer = Writer::createFromPath( 'php://temp', 'w+');
		$writer->insertOne( $_POST['post_meta'] );
		$writer->insertAll( $posts );
		$writer->output('file.csv');
		die();
	}
}
