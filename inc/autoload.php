<?php

defined( 'ABSPATH' ) || exit;

function gstp_autoload( $class ) {
	if ( false === strpos( $class, 'GSTP_' ) ) {
		return;
	}

	$file_name = 'class-' . str_replace( ['_', '\\'], ['-', DIRECTORY_SEPARATOR], strtolower( $class ) ) . '.php';
	$file_path = GSTP_PATH . "inc/classes/{$file_name}";

	if ( file_exists( $file_path ) ) {
		include_once $file_path;
	}
}
spl_autoload_register( 'gstp_autoload' );
