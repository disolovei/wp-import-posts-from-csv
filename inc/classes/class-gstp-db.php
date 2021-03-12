<?php

defined( 'ABSPATH' ) || exit;

class GSTP_DB {

	public static function update_post_translations( $target_blog_id, $translation_for_post_id, $locale, $translation_id ) {
		if ( 0 >= $target_blog_id || ! is_numeric( $target_blog_id ) ) {
			$target_blog_id = get_current_blog_id();
		}

		if ( ! GSTP_Helper::blog_exists( $target_blog_id ) ) {
			return false;
		}

		if ( '' === $locale ) {
			$locale = GSTP_Helper::get_blog_language( $target_blog_id );
		}

		$related_post_translations = get_blog_option( $target_blog_id, 'msls_' . $translation_for_post_id );

		if ( is_array( $related_post_translations ) ) {

			if ( array_key_exists( $locale, $related_post_translations ) ) {
				self::delete_translation(
					GSTP_Helper::get_blog_by_lang( $locale ),
					$related_post_translations[$locale],
					$locale
				);
			}

			$related_post_translations[$locale] = $translation_id;
		} else {
			$related_post_translations = [
				$locale => $translation_id,
			];
		}

		return update_blog_option(
			$target_blog_id,
			'msls_' . $translation_for_post_id,
			$related_post_translations
		);
	}

	public static function delete_translation( $delete_for_blog_with_id, $delete_for_post_with_id, $translation_locale ) {
		if ( GSTP_Helper::blog_exists( $delete_for_blog_with_id ) ) {
			return false;
		}

		$translations = get_blog_option( $delete_for_blog_with_id, 'msls_' . $delete_for_post_with_id, [] );
		unset( $translations[$translation_locale] );
		return update_blog_option( $delete_for_blog_with_id, 'msls_' . $delete_for_post_with_id, $translations );
	}
}
