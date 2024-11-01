<?php
/**
 * Plugin Name:  Whippet
 * Plugin URI:   https://whippetwp.com/
 * Description:  Speed up your Website using Whippet
 * Version:      1.0.1
 * Author:       Jake Henshall
 * Author URI:   https://jake.hen.sh/all/
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  whippet
 * Domain Path:  /languages
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Add our plugin menu.
 *
 * @var [type]
 */
if ( is_admin() ) {
	add_action( 'admin_menu', 'whippet_menu', 9 );
}

/**
 * Add Submenu
 */
function whippet_menu() {
	$pages = add_submenu_page( 'tools.php', 'Whippet', 'Whippet', 'manage_options', 'whippet', 'whippet_admin' );
}

/**
 * Adding settings link
 * @param [type] $links [description]
 */
function whippet_plugin_add_settings_link( $links ) {
	$links = array_merge( array(
		'<a href="' . esc_url( admin_url( 'tools.php?page=whippet' ) ) . '">' . __( 'Settings', 'whippet' ) . '</a>'
	), $links );
	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'whippet_plugin_add_settings_link' );

/**
 * Plugin admin/settings page.
 */
function whippet_admin() {
	include plugin_dir_path( __FILE__ ) . '/inc/admin.php';
}

/**
 * Initialize WP_Filesystem if hasn't inited yet.
 */
function whippet_init_wp_filesystem() {
	global $wp_filesystem;
	if ( null === $wp_filesystem ) {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
	}
}

/**
 * Plugin admin scripts.
 */
function whippet_admin_scripts() {

		wp_register_style( 'whippet-styles', plugins_url( '/dist/css/style.css', __FILE__ ), array(), '0.0.1' );
		wp_enqueue_style( 'whippet-styles' );

}
add_action( 'admin_enqueue_scripts', 'whippet_admin_scripts' );

// all plugin file includes.
require plugin_dir_path( __FILE__ ) . '/inc/settings.php';
require plugin_dir_path( __FILE__ ) . '/inc/functions.php';
require plugin_dir_path( __FILE__ ) . '/inc/script-manager.php';
require plugin_dir_path( __FILE__ ) . '/inc/save-ga-local.php';
require plugin_dir_path( __FILE__ ) . '/inc/import-export.php';
/**
 * Whippet pre-configuration
 * ============================================================================
 */
register_activation_hook( __FILE__, 'whippet_install' );

global $whippet_db_version;
$whippet_db_version = 1.1;

/**
 * Install required tabled:
 * whippet_disabled, whippet_enabled
 */
function whippet_install() {
	if ( ! wp_next_scheduled( 'update_local_ga' ) ) {
		wp_schedule_event( time(), 'daily', 'update_local_ga' );
	}
	global $wpdb;
	global $whippet_db_version;

	$charset_collate = $wpdb->get_charset_collate();
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table_name  = $wpdb->prefix . 'whippet_disabled';
	$sql_whippet = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		handler_type tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=css, 1=js',
		handler_name varchar(128) DEFAULT '' NOT NULL,
		url varchar(255) DEFAULT '' NOT NULL,
		PRIMARY KEY (id)
	) $charset_collate;";

	$table_name             = $wpdb->prefix . 'whippet_enabled';
	$sql_whippet_exceptions = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		handler_type tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=css, 1=js',
		handler_name varchar(128) DEFAULT '' NOT NULL,
		content_type varchar(64) DEFAULT '' NOT NULL,
		url varchar(255) DEFAULT '' NOT NULL,
		PRIMARY KEY (id)
	) $charset_collate;";

	dbDelta( $sql_whippet );
	dbDelta( $sql_whippet_exceptions );

	update_option( 'whippet_db_version', $whippet_db_version );

}

ob_start();
