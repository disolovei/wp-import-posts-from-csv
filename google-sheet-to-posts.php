<?php
/**
 * Plugin Name: Google Sheet to Posts
 * Version: 0.8
 * Author: Dima Solovei
 */

defined( 'ABSPATH' ) || exit;

define( 'GSTP_PATH', plugin_dir_path( __FILE__ ) );
define( 'GSTP_URL', plugin_dir_url( __FILE__ ) );
define( 'GSTP_MAIN_FILE', __FILE__ );

include_once GSTP_PATH . 'inc/autoload.php';
include_once GSTP_PATH . 'inc/classes/class-google-sheet-to-posts.php';

$GLOBALS['GSTP'] = Google_Sheet_To_Posts::get_instance();
