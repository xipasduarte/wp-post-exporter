=== Plugin Name ===
Contributors: xipasduarte
Donate link: https://github.com/xipasduarte/wp-post-exporter
Tags: export, csv
Requires at least: 4.6
Tested up to: 4.9.1
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Export your posts to CSV.

== Description ==

With this plugin you'll be able to select which data to export to the generated 
files.

Here are the current available fields for selection:

*   Post Types
*   Post Status
*   Post Meta

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/wp-post-exporter` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Tools->WP Post Exporter screen to start exporting


== Frequently Asked Questions ==

= Will there be other output formats? =

Yes, currently in the pipeline are JSON and plain text. You can submit any that you'd like to see supported.

= Will there be more field options? =

Yes, the aim is to have all fields exposed, even private ones (although the approach for these is still being considered).

= Will there be more filtering options? =

Yes. Currently in the pipeline are: terms, dates and date spans. You can submit other you'd like to see supported.

== Screenshots ==

1. /assets/screenshot-1.png

== Changelog ==

= 1.0.2 =
* Correct readme contents
* Add screenshot-1.png

= 1.0.1 =
* Adding more information to composer.json

= 1.0.0 =
* Initial concept version with support for post type, status and meta. Exports only to CSV.
