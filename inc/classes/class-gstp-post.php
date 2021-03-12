<?php

defined( 'ABSPATH' ) || exit;

class GSTP_Post {

	protected $args = [];

	protected $ID = 0;

	protected $target_blog_id = 0;

	protected $related_post_id = 0;

	protected $current_post_translations = null;

	protected $related_post = null;

	protected $post_type = 'post';

	public function __construct( $args ) {
		if ( ! empty( $args['blog_id'] ) ) {
			$this->target_blog_id = absint( $args['blog_id'] );
			unset( $args['blog_id'] );
		}

		if ( ! empty( $args['related_post_id'] ) ) {
			$this->related_post_id = absint( $args['related_post_id'] );
			unset( $args['related_post_id'] );
		}

		$this->args = $this->prepare_args( $args );
	}

	public function get_ID() {
		return $this->ID;
	}

	public function get_related_post_id() {
		return $this->related_post_id;
	}

	public function get_args() {
		return $this->args;
	}

	public function save() {
		$new_post_id = wp_insert_post( $this->args);

		if ( is_wp_error( $new_post_id ) ) {
			return false;
		}

		$this->ID = $new_post_id;

		//hreflang
		$this->tie_up_posts();

		return true;
	}

	protected function tie_up_posts() {
		if ( ! $this->check_requirements() ) {
			return false;
		}

		$current_blog_locale = GSTP_Helper::get_blog_language();

		switch_to_blog( $this->target_blog_id );

		$this->related_post = get_post( $this->related_post_id );

		if ( is_null( $this->related_post ) || $this->post_type !== $this->related_post->post_type ) {
			return false;
		}

		restore_current_blog();

		$related_post_translations = get_blog_option( $this->target_blog_id, 'msls_' . $this->related_post->ID );

		if ( is_array( $related_post_translations ) ) {
			$this->current_post_translations = $related_post_translations;
			$related_post_translations[$current_blog_locale] = $this->ID;
		} else {
			$this->current_post_translations = [];
			$related_post_translations = [
				$current_blog_locale => $this->ID,
			];
		}

		update_blog_option(
			$this->target_blog_id,
			'msls_' . $this->related_post->ID,
			$related_post_translations
		);

		if ( 1 < count( $related_post_translations ) ) {
			foreach ( $related_post_translations as $locale => $locale_post_id ) {
				if ( $locale === $current_blog_locale ) {
					continue;
				}

				$blog_id = GSTP_Helper::get_blog_by_lang( $locale );

				if ( 0 === $blog_id ) {
					continue;
				}

				GSTP_DB::update_post_translations(
					$blog_id,
					$locale_post_id,
					$locale,
					$this->get_ID()
				);
			}
		}

		if ( array_key_exists( $current_blog_locale, $this->current_post_translations ) ) {
			unset( $this->current_post_translations[$current_blog_locale] );
		}

		$related_blog_locale = GSTP_Helper::get_blog_language( $this->target_blog_id );
		$this->current_post_translations[$related_blog_locale] = $this->get_related_post_id();

		return update_blog_option(
			get_current_blog_id(),
			'msls_' . $this->get_ID(),
			$this->current_post_translations
		);
	}

	protected function check_requirements() {
		if ( 0 === $this->ID ) {
			return false;
		}

		if ( ! is_multisite() ) {
			return false;
		}

		if ( $this->target_blog_id <= 0 || $this->related_post_id <= 0 ) {
			return false;
		}

		if ( ! GSTP_Helper::blog_exists( $this->target_blog_id ) ) {
			return false;
		}

		return true;
	}

	protected function prepare_args( $args ) {
		$args = wp_parse_args( $args, [
			'post_title'    => '',
			'post_content'  => '',
			'post_type'     => 'post',
			'post_status'   => 'publish',
		] );

		if ( ! in_array( $args['post_type'], ['post', 'page'] ) ) {
			$args['post_type'] = 'post';
		}

		if ( ! empty( $args['post_content'] ) ) {
			$post_baker = new GSTP_Post_Baker( $args['post_content'] );

			if ( '' === $args['post_title'] && $post_baker->has_title() ) {
				$args['post_title'] = $post_baker->get_title();
			}

			$args['post_content'] = $post_baker->get_content();
		}

		$this->post_type = $args['post_type'];

		$args['post_author'] = get_current_user_id();

		return wp_slash( $args );
	}
}
