<?php

defined( 'ABSPATH' ) || exit;

class GSTP_Post_Baker {

	protected $content = '';

	protected $title = '';

	public function __construct( $content ) {
		if ( is_string( $content ) && ! empty( $content ) ) {
			$this->prepare_content( $content );
		}
	}

	public function get_content() {
		return $this->content;
	}

	public function get_title() {
		return $this->title;
	}

	public function has_title() {
		return '' !== $this->title;
	}

	protected function prepare_content( $content ) {
		$this->prepare_title( $content );
		$this->replace_images( $content );

		$this->content = trim( $content );
	}

	protected function prepare_title( &$content ) {
		$title_pattern = ':<h1>(.*)</h1>:U';

		preg_match( $title_pattern, $content, $matches );

		if ( ! empty( $matches[1] ) ) {
			$this->title = $matches[1];
//			$content = preg_replace( $title_pattern, '', $content, 1 );
		}
	}

	protected function replace_images( &$content ) {
		$image_pattern = '#\[(\[?)(img)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)#Um';

		preg_match_all( $image_pattern, $content, $matches );

		if ( ! empty( $matches[0] ) && is_array( $matches[0] ) && 0 !== count( $matches[0] ) ) {
			$shortcodes = array_unique( $matches[0] );

			foreach ( $shortcodes as $shortcode ) {
				$content = str_replace( $shortcode, do_shortcode( $shortcode ), $content );
			}
		}
	}
}
