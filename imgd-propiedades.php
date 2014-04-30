<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   IMGD Propiedades
 * @author    Federico Reinoso <admin@imgdigital.com.ar>
 * @license   GPL-2.0+
 * @link      http://imgdigital.com.ar
 * @copyright 2014 Federico Reinoso - IMGDigital
 *
 * @wordpress-plugin
 * Plugin Name:       IMGD Propiedades
 * Plugin URI:        http://imgdigital.com.ar/imgdpropieades
 * Description:       Este es un plug-in para wordpress para el manejo de inmobiliarias
 * Version:           1.0.0
 * Author:            Federico Reinoso
 * Author URI:        http://about.me/bicho44
 * Text Domain:       imgdigital
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/bicho44/imgd_propiedades
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * IMGD Propiedades
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-imgd-propiedades.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'IMGD_Propiedades', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'IMGD_Propiedades', 'deactivate' ) );

/*
 * Carga IMGD_Propiedades
 */
add_action( 'plugins_loaded', array( 'IMGD_Propiedades', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * @TODO:
 *
 * - replace `class-plugin-name-admin.php` with the name of the plugin's admin file
 * - replace Plugin_Name_Admin with the name of the class defined in
 *   `class-plugin-name-admin.php`
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
/*if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-plugin-name-admin.php' );
	add_action( 'plugins_loaded', array( 'Plugin_Name_Admin', 'get_instance' ) );

}*/
