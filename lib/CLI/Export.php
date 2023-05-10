<?php

namespace xipasduarte\WP\Plugin\PostExporter\CLI;

use League\Csv\Writer;
use WP_CLI_Command;
use xipasduarte\WP\Plugin\PostExporter\Export as PostExporterExport;

class Export extends WP_CLI_Command {

	/**
	 * Export posts into a csv.
	 *
	 * ## OPTIONS
	 *
	 * <config_path>
	 * : Path to the export configuration file (must be php).
	 *
	 * <export_path>
	 * : Path for the exported csv.
	 *
	 * @since 0.0.0
	 *
	 * @param array $args
	 * @param array $assoc_args
	 * @return void
	 */
	public function export( array $args, array $assoc_args ) : void {
		if ( ! str_ends_with( $args[0], '.php' ) ) {
			\WP_CLI::error( 'Config file passed is not php.' );
		}

		$config      = include $args[0];
		$export_file = $args[1];

		$export = ( new PostExporterExport() )->export( $config );

		$writer = Writer::createFromString();
		$writer->insertAll( $export );
		$csv = $writer->toString();

		$file = fopen( $export_file, 'w+' );
		fwrite( $file, $csv );
	}
}
