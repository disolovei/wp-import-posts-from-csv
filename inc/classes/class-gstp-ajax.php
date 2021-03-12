<?php

defined( 'ABSPATH' ) || exit;

class GSTP_AJAX {

	public function __construct() {
		add_action( 'wp_ajax_gstp_save_post', [$this, 'save_post'] );
	}

	public function save_post() {
		$post_title 		= filter_input( INPUT_POST, 'postTitle', FILTER_SANITIZE_STRING );
		$post_content 		= filter_input( INPUT_POST, 'postContent', FILTER_SANITIZE_STRING );
		$related_post_id	= filter_input( INPUT_POST, 'relatedPostID', FILTER_SANITIZE_NUMBER_INT );
		$blog_id            = filter_input( INPUT_POST, 'blogId', FILTER_SANITIZE_NUMBER_INT );
		$post_type			= filter_input( INPUT_POST, 'postType', FILTER_SANITIZE_STRING );

		$insert_post_args = [
			'post_title'	    => $post_title,
			'post_content'	    => $post_content,
			'post_author'	    => get_current_user_id(),
			'post_type'		    => $post_type,
			'blog_id'           => $blog_id,
			'related_post_id'   => $related_post_id,
		];

		$post = new GSTP_Post( $insert_post_args );

		if ( $post->save() ) {
			wp_send_json_success(
				sprintf(
					'<a href="%s" target="_blank">Edit</a> | <a href="%s" target="_blank">Visit</a>',
					get_edit_post_link( $post->get_ID() ),
					get_the_permalink( $post->get_ID() )
				)
			);
		}

		wp_send_json_error( 'Something wrong!' );
	}
}
