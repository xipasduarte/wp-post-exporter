<?php

namespace xipasduarte\WP\Plugin\PostExporter;

use Exception;

class Export {

	// Post export.
	public function export( array $config ) {
		try {
			$this->validate_config( $config );
		} catch ( Exception $e ) {
			error_log( $e->getTraceAsString() );
			\WP_CLI::error( $e->getMessage() );
		}

		$post_ids = $this->query( $config['query'] );

		$export_data = [ [] ]; // Empty first array is for labels.

		foreach ( $post_ids as $ID ) {
			$export_post_data = $this->get_post_export( $ID, $config['data'] );
			$export_data[] = $export_post_data;
		}

		if ( count( $export_data ) === 1 ) {
			return [];
		}

		$export_data[0] = array_keys( $export_data[1] );

		return $export_data;
	}

	private function query( array $query_args ) : array {
		$query_args['fields'] = 'ids';
		$query = new \WP_Query( $query_args );

		return $query->get_posts();
	}

	private function get_post_export( int $post_id, array $data_config ) : array {
		$post_data = [];
		foreach ( $data_config as $config_part ) {
			switch ( $config_part['type'] ) {
				case 'post_data':
					$post_data = $this->get_post_data( $post_id, $config_part, $post_data );
					break;
				case 'meta_data':
					$post_data = $this->get_post_meta( $post_id, $config_part, $post_data );
					break;
				case 'term_data':
					$post_data = $this->get_post_tax( $post_id, $config_part, $post_data );
					break;
				default:
					break;
			}
		}
		return $post_data;
	}

	private function get_post_data( int $post_id, array $config, array $post_data ) : array {
		$post  = \get_post( $post_id );
		$field = $config['name'];
		if ( ! isset( $post->$field ) ) {
			$post_data[ $field ] = 'NULL';
			return $post_data;
		}

		// Check the data entry for transforms on the post_data
		$value = $post->$field;
		$key   = $field;
		if ( isset( $config['rename'] ) ) {
			$key = $config['rename'];
		}

		// TODO: add filter

		// Add it to the export array, with key => data
		$post_data[ $key ] = $value;
		return $post_data;
	}

	private function get_post_meta( int $post_id, array $config, array $post_data ) : array {
		$meta_key   = $config['name'];
		$meta_value = \get_post_meta( $post_id, $meta_key );
		if ( empty( $meta_value ) ) {
			$post_data[ $meta_key ] = 'NULL';
			return $post_data;
		}

		$meta_value = array_map( 'maybe_unserialize', $meta_value );

		// Check the data entry for transforms on the post_meta

		$value = $meta_value;
		$key   = $meta_key;
		if ( isset( $config['rename'] ) ) {
			$key = $config['rename'];
		}

		// TODO: add filter

		// TODO: relational_mapping

		// TODO: When we add the filter, we need to be careful if the value returned is not in the original post meta list of values.
		if ( is_array( $value ) ) {
			$string_value = '';
			if ( count( $value ) === 1 ) {
				$string_value = is_array( $value[0] ) ? implode( ',', $value[0] ) : $value[0];

			} else if ( count( $value ) > 1 ) {
				$mapped_value = array_map(
					function ( $val ) {
						if ( is_array( $val ) ) {
							return '[' . implode( ',', $val ) . ']';
						}
						return "[{$val}]";
					},
					$value
				);
				$string_value = implode( ',', $mapped_value );
			}

			$value = $string_value;
		}

		// Add it to the export array, with key => data
		$post_data[ $key ] = $value;
		return $post_data;
	}

	private function get_post_tax( int $post_id, array $config, array $post_data ) : array {
		$taxonomy = $config['taxonomy'];
		$terms    = \wp_get_object_terms( $post_id, $taxonomy );
		if ( \is_wp_error( $terms ) ) {
			$post_data[ $taxonomy ] = 'NULL';
			return $post_data;
		}

		$field = $config['name'];

		$value = [];
		foreach ( $terms as $term ) {
			// TODO: add filter
			// TODO: relational_mapping

			$value[] = $term->$field;
		}

		$value = implode( ',', $value );

		$post_data[ $taxonomy ] = $value;
		return $post_data;
	}

	private function validate_config( array $config ) {
		if ( ! isset( $config['query'] ) ) {
			throw new Exception( "No 'query' entry in export config." );
		}
		if ( ! isset( $config['data'] ) ) {
			throw new Exception( "No 'data' entry in export config." );
		}

		foreach ( $config['data'] as $index => $config_data ) {
			if ( ! isset( $config_data['type'] ) ) {
				throw new Exception( "No 'type' entry in config's data entry at index '{$index}'." );
			}
			$type = $config_data['type'];
			switch ( $type ) {
				case 'post_data':
				case 'meta_data':
					break;
				case 'term_data':
					if ( ! isset( $config_data['taxonomy'] ) ) {
						throw new Exception( "No 'taxonomy' entry in config's data entry at index '{$index}'." );
					}
					break;
				default:
					throw new Exception( "Unknown type '{$type}' in config's data entry at index '{$index}'." );
			}

			if ( ! isset( $config_data['name'] ) ) {
				throw new Exception( "No 'name' entry in config's data entry at index '{$index}'." );
			}

			if (
				isset( $config_data['rename'] )
				&& ! is_string( $config_data['rename'] )
				&& ! is_int( $config_data['rename'] )
			) {
				throw new Exception( "The 'rename' entry in config's data entry at index '{$index}' is not a string or an int." );
			}
		}
	}
}
