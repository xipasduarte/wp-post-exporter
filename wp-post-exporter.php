<?php
/**
 * @wordpress-plugin
 * Plugin Name: WP Post Exporter
 * Plugin URI:  https://github.com/xipasduarte/wp-post-exporter
 * Description: Export any post type to CVS.
 * Version:     1.0.0
 * Author:      Pedro Duarte
 * Author URI:  https://github.com/xipasduarte
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-post-exporter
 * Domain Path: /languages
 */

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in lib/Activator.php
 */
\register_activation_hook( __FILE__, '\xipasduarte\WP\Plugin\PostExporter\Activator::activate' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in lib/Deactivator.php
 */
\register_deactivation_hook( __FILE__, '\xipasduarte\WP\Plugin\PostExporter\Deactivator::deactivate' );

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
\add_action( 'plugins_loaded', function () {
	$plugin = new xipasduarte\WP\Plugin\PostExporter\Plugin( 'wp-post-exporter', '1.0.0' );
	$plugin->run();
} );
