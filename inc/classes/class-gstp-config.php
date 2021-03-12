<?php

defined( 'ABSPATH' ) || exit;

final class GSTP_Config {

	protected $api_key = '';

	protected $client_id = '';

	protected $sheet_id = '';

	protected $sheet_name = '';

	public function __construct() {
		$this->client_id    = get_option( 'google_sheet_to_posts_client_id' );
		$this->api_key      = get_option( 'google_sheet_to_posts_api_key' );
		$this->sheet_id     = get_option( 'google_sheet_to_posts_sheet_id' );
		$this->sheet_name   = get_option( 'google_sheet_to_posts_sheet_name' );
	}

	public function get_api_key() {
		return $this->api_key;
	}

	public function get_client_id() {
		return $this->client_id;
	}

	public function get_sheet_id() {
		return $this->sheet_id;
	}

	public function get_sheet_name() {
		return $this->sheet_name;
	}
}
