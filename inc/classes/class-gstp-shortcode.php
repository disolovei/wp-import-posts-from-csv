<?php

defined( 'ABSPATH' ) || exit;

class GSTP_Shortcode {
	public function __construct() {
		add_shortcode( 'img', [$this, 'img_callback'] );
	}

	public function img_callback( $atts ) {
		$atts = array_combine(
			array_map( 'esc_attr', array_keys( $atts ) ),
			array_map( 'esc_attr', array_values( $atts ) )
		);

		$atts = shortcode_atts( [
			'src'       => '',
			'alt'       => '',
			'decoding'  => 'async',
			'loading'   => 'async',
			'width'     => '',
			'height'    => '',
		], $atts );

		if ( '' === $atts['src'] ) {
			return '';
		}

		$img_tag_attr = '';

		foreach ( $atts as $attr_name => $attr_value ) {
			$img_tag_attr .= sprintf( ' %s="%s"', $attr_name, $attr_value );
		}

		return sprintf( '<img%s/>', $img_tag_attr );
	}
}
