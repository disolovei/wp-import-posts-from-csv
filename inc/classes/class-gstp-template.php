<?php

defined( 'ABSPATH' ) || exit;

class GSTP_Template {
	public static function settings_page() {
		$active_tab = ! empty( $_GET['tab'] ) ? strip_tags( $_GET['tab'] ) : 'file-reader';
		?>

		<div class="wrap gstp-reader">
			<h2><?php echo get_admin_page_title() ?></h2>

			<?php if ( ! is_plugin_active( 'multisite-language-switcher/MultisiteLanguageSwitcher.php' ) ) {
				echo '<h3>To automatically generate hreflang, install and activate the <a href="https://wordpress.org/plugins/multisite-language-switcher/" target="_blank">Multisite Language Switcher</a> plugin!</h3>';
			} ?>

			<p class="nav-tab-wrapper">
				<a href="?page=<?php echo Google_Sheet_To_Posts::ADMIN_PAGE_SLUG; ?>&tab=file-reader" class="nav-tab<?php echo 'file-reader' == $active_tab ?  ' nav-tab-active': ''; ?>">File Reader</a>
				<a href="?page=<?php echo Google_Sheet_To_Posts::ADMIN_PAGE_SLUG; ?>&tab=api-reader" class="nav-tab<?php echo 'api-reader' == $active_tab ?  ' nav-tab-active': ''; ?>">API Reader</a>
				<a href="?page=<?php echo Google_Sheet_To_Posts::ADMIN_PAGE_SLUG; ?>&tab=settings" class="nav-tab<?php echo 'settings' == $active_tab ?  ' nav-tab-active': ''; ?>">API Settings</a>
			</p>
			<style>
                .hide {
                    display: none!important;
                }
                .posts-list th, .posts-list td {
                    padding: 5px;
                    border: 1px solid black;
                }
                .log--error {
                    color: red;
                }

                .log--success {
                    color: green;
                }
			</style>

			<?php if ( 'settings' === $active_tab ) {
				self::settings_output();
			} elseif ( 'file-reader' === $active_tab ) {
				self::file_reader_output();
			} else {
				self::api_reader_output();
			} ?>

		</div>

		<?php
	}

	public static function credentials_notice() {
		echo '<p>Please fill credentials fields! You can get all required data on <a href="https://developers.google.com/sheets/api/quickstart/js" target="_blank">this page</a> or from Google API Console!</p>';
	}

	protected static function file_reader_output() {
		$sites	= GSTP()->get_blogs();
		?>
			<div id="file-reader">
				<form method="POST" onsubmit="return false;" id="read-form">
					<p>
						<label>Please select CSV file: <input type="file" name="gstp-csv-file" id="gstp-csv-file" required accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
						<button type="button" class="button-primary" id="load-data">Read</button></label>
					</p>
				</form>
				<div class="auth-success hide" style="margin-top: 1em">
					<button type="button" id="save-data" class="button button-primary">Save data</button>
					<span style="margin-left: 15px;vertical-align: middle;display: inline-block;font-weight: 700;">Total items: <span id="total-items">0</span></span>

					<?php if ( 0 !== count( $sites ) ) : ?>

						<label style="float:right;"><b>Merge via blog:</b>
							<select name="merge-via-blog" id="merge-via-blog">
								<option selected disabled value="-1">Select blog</option>

									<?php foreach ( $sites as $site ) {
										printf( '<option value="%d">%s</option>', $site->blog_id, "{$site->domain}{$site->path}" );
									} ?>

								</select>
							</label>

						<?php endif; ?>

				</div>
				<hr>
				<div style="display:flex;justify-content: space-between;margin-top: 1em;">
					<div class="table-wrapper auth-success" style="max-height:500px;overflow:auto;display:inline-block;width:68%">
						<table class="posts-list" style="border-collapse: collapse;width:100%">
							<thead>
								<tr>
									<th>Post Title</th>
									<th>Related post ID</th>
									<th>Post type</th>
									<th>Result</th>
								</tr>
							</thead>
							<tbody id="posts-list-tbody"></tbody>
						</table>
					</div>
					<pre id="logs" style="white-space: pre-wrap;width: 30%;padding: 15px;margin: 0 0 0 15px;background-color: #e3e3e3;"></pre>
				</div>
			</div>

		<?php
	}

	protected static function settings_output() {
		?>

			<form action="options.php" method="POST">

				<?php
					settings_fields( GSTP_Helper::get_settings_group_name() );
					do_settings_sections( Google_Sheet_To_Posts::ADMIN_PAGE_SLUG );
					submit_button();
				?>

			</form>

		<?php
	}

	protected static function api_reader_output() {
		if ( ! GSTP()->is_ready() ) {
			GSTP_Template::credentials_notice();
			echo '<a href="' . admin_url( '/admin.php?page=google_sheet_to_posts&tab=settings' ) . '">Go to Settings page</a>';
			return;
		}

		$sites	= GSTP()->get_blogs();
		?>

			<div id="api-reader">
				<div style="padding-top: 30px">
					<div class="auth-success hide">
						<span style="font-size: 1.5rem;font-weight: bold;display: inline-block;vertical-align: -webkit-baseline-middle;">You are logged in!</span>
						<button type="button" id="signout_button" class="button button-primary">Sign Out</button>
					</div>
					<div class="auth-failed hide">
						<span style="font-size: 1.5rem;font-weight: bold;display: inline-block;vertical-align: -webkit-baseline-middle;">You are not logged in!</span>
						<button type="button" id="authorize_button" class="button button-primary">Authorize</button>
					</div>
				</div>
				<hr>
				<div class="auth-success hide">
					<div>
						<button type="button" id="load-data" class="button button-primary">Load data</button>
						<button type="button" id="save-data" class="button button-primary hide">Save data</button>
						<span style="margin-left: 15px;vertical-align: middle;display: inline-block;font-weight: 700;">Total items: <span id="total-items">0</span></span>

						<?php if ( 0 !== count( $sites ) ) : ?>

							<label style="float:right;"><b>Merge via blog:</b>
								<select name="merge-via-blog" id="merge-via-blog">
									<option selected disabled value="-1">Select blog</option>

									<?php foreach ( $sites as $site ) {
										printf( '<option value="%d">%s</option>', $site->blog_id, "{$site->domain}{$site->path}" );
									} ?>

								</select>
							</label>

						<?php endif; ?>

					</div>
					<hr>
					<div style="display:flex;justify-content: space-between">
						<div class="table-wrapper auth-success" style="max-height:500px;overflow:auto;display:inline-block;width:68%">
							<table class="posts-list" style="border-collapse: collapse;width:100%">
								<thead>
								<tr>
									<th>Post Title</th>
									<th>Related post ID</th>
									<th>Post type</th>
									<th>Result</th>
								</tr>
								</thead>
								<tbody id="posts-list-tbody"></tbody>
							</table>
						</div>
						<pre id="logs" style="white-space: pre-wrap;width: 30%;padding: 15px;margin: 0 0 0 15px;background-color: #e3e3e3;"></pre>
					</div>
				</div>
			</div>

		<?php
	}
}
