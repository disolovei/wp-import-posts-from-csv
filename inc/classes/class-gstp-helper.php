<?php

defined( 'ABSPATH' ) || exit;

class GSTP_Helper {

	public static function get_settings_group_name() {
		return Google_Sheet_To_Posts::ADMIN_PAGE_SLUG . '_group';
	}

	public static function get_sites() {
		if ( ! is_multisite() ) {
			return [];
		}

		$sites = get_sites();

		if ( 1 >= count( $sites ) ) {
			return [];
		}

		$current_blog_id = get_current_blog_id();

		foreach ( $sites as $site_index => $site ) {
			if ( $current_blog_id === (int)$site->blog_id ) {
				unset( $sites[$site_index] );
				continue;
			}

			$sites[$site_index]->locale = self::get_blog_language( $site->blog_id );
		}

		return $sites;
	}

	public static function blog_exists( $blog_id ) {
		$sites = GSTP()->get_blogs();

		if ( 0 === count( $sites ) ) {
			return false;
		}

		foreach ( $sites as $site ) {
			if ( (int)$site->blog_id === $blog_id ) {
				return true;
			}
		}

		return false;
	}

	public static function get_blog_language( $blog_id = null, $default = 'en_US' ) {
		if ( null === $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		$language = (string)get_blog_option( $blog_id, 'WPLANG' );

		return '' !== $language ? $language : $default;
	}

	public static function get_blog_by_lang( $locale = 'en_US' ) {
		$sites = GSTP()->get_blogs();

		if ( 0 !== count( $sites ) ) {
			foreach ( $sites as $site ) {
				if ( $locale === $site->locale ) {
					return (int)$site->blog_id;
				}
			}
		}

		$current_blog_id = get_current_blog_id();

		if ( (string)get_blog_option( $current_blog_id, 'WPLANG' ) === $locale ) {
			return $current_blog_id;
		}

		return 0;
	}

}
