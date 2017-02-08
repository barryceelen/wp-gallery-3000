<?php
/**
 * Main plugin file
 *
 * @package   Gallery_3000
 * @author    Barry Ceelen
 * @license   GPL-3.0+
 * @link      https://github.com/barryceelen/wp-gallery-3000
 * @copyright Barry Ceelen
 *
 * Plugin Name: Gallery 3000
 * Plugin URI: https://github.com/barryceelen/wp-gallery-3000
 * Description: Adds an image gallery meta box to the post and page edit screen.
 * Author: Barry Ceelen
 * Version: 1.0.0
 * Author URI: https://github.com/barryceelen
 * Text Domain: gallery-3000
 * Domain Path: /languages/
 */

// Don't load directly.
defined( 'ABSPATH' ) or die();

define( 'GALLERY_3000_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GALLERY_3000_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( is_admin() ) {

	require_once( GALLERY_3000_PLUGIN_DIR . '/includes/class-gallery-3000.php' );

	add_action( 'after_setup_theme', array( 'Gallery_3000', 'get_instance' ) );
	add_action( 'init', 'gallery_3000_load_textdomain' );
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function gallery_3000_load_textdomain() {

	if ( false !== strpos( __FILE__, basename( WPMU_PLUGIN_DIR ) ) ) {
		load_muplugin_textdomain( 'gallery-3000', basename( dirname( __FILE__ ) ) . '/languages' );
	} else {
		load_plugin_textdomain( 'gallery-3000', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
}
