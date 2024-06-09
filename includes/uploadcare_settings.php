<?php

$uc_tabs      = array(
	'file',
	'camera',
	'url',
	'facebook',
	'gdrive',
	'gphotos',
	'dropbox',
	'instagram',
	'evernote',
	'flickr',
	'onedrive',
	'box',
	'huddle',
	'vk',
);
$tab_defaults = array(
	'file',
	'camera',
	'url',
	'dropbox',
	'facebook',
	'instagram',
	'gdrive',
	'gphotos',
);

$allowed_html = array(
	'a'      => array(
		'href'  => array(),
		'title' => array(),
	),
	'br'     => array(),
	'em'     => array(),
	'strong' => array(),
);

$saved       = false;
$code_errors = array();
$keys_exists = false;
if ( isset( $_POST['uploadcare_hidden'] ) && 'Y' === $_POST['uploadcare_hidden'] ) {
	if ( empty( $_REQUEST['uc_save_nonce'] ) || ! wp_verify_nonce(
		sanitize_text_field(
			wp_unslash( $_REQUEST['uc_save_nonce'] )
		),
		'uc_save_action'
	)
	) {
		return;
	}
	$uploadcare_public_var = isset( $_POST['uploadcare_public'] ) ? sanitize_text_field( wp_unslash( $_POST['uploadcare_public'] ) ) : '';
	$uploadcare_secret_var = isset( $_POST['uploadcare_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['uploadcare_secret'] ) ) : '';

	$save_action       = true;
	$uploadcare_public = $uploadcare_public_var;
	$uploadcare_secret = $uploadcare_secret_var;
	if ( $uploadcare_public && $uploadcare_secret ) {
		$keys_exists = true;
		uc_save_api_keys( $uploadcare_public, $uploadcare_secret );
	}

	$uploadcare_cdn_base_var = isset( $_POST['uploadcare_cdn_base'] ) ? sanitize_text_field( wp_unslash( $_POST['uploadcare_cdn_base'] ) ) : '';
	$uploadcare_cdn_base     = \str_replace( 'https://', '', $uploadcare_cdn_base_var );
	update_option( 'uploadcare_cdn_base', $uploadcare_cdn_base );
	$uploadcare_upload_lifetime = isset( $_POST['uploadcare_upload_lifetime'] ) ? sanitize_text_field( wp_unslash( $_POST['uploadcare_upload_lifetime'] ) ) : '';
	update_option( 'uploadcare_upload_lifetime', $uploadcare_upload_lifetime );
	$uploadcare_finetuning = isset( $_POST['uploadcare_finetuning'] ) ? sanitize_text_field( wp_unslash( $_POST['uploadcare_finetuning'] ) ) : '';
	update_option( 'uploadcare_finetuning', $uploadcare_finetuning );

	if ( empty( $_POST['uploadcare_source_tabs'] ) ) {
		$uploadcare_source_tabs[0] = 'file';
		$code_errors[]             = __( 'Select at least one source' );
	} else {
		$uploadcare_source_tabs = array_map( 'sanitize_text_field', wp_unslash( $_POST['uploadcare_source_tabs'] ) );
	}

	update_option( 'uploadcare_source_tabs', $uploadcare_source_tabs );
	$uploadcare_adaptive_delivery = isset( $_POST['uploadcare_adaptive_delivery'] ) ? 1 : 0;
	update_option( 'uploadcare_adaptive_delivery', $uploadcare_adaptive_delivery );
	$saved = true;
} else {
	$save_action                  = false;
	$uploadcare_public            = \trim( get_option( 'uploadcare_public' ) );
	$uploadcare_secret            = \trim( get_option( 'uploadcare_secret' ) );
	$uploadcare_cdn_base          = \trim( get_option( 'uploadcare_cdn_base', 'ucarecdn.com' ) );
	$uploadcare_finetuning        = \trim( get_option( 'uploadcare_finetuning' ) );
	$uploadcare_blink_loader      = \trim( get_option( 'uploadcare_blink_loader' ) );
	$uploadcare_source_tabs       = get_option( 'uploadcare_source_tabs', $tab_defaults );
	$uploadcare_upload_lifetime   = get_option( 'uploadcare_upload_lifetime', '0' );
	$uploadcare_adaptive_delivery = get_option( 'uploadcare_adaptive_delivery', false );
}

$admin         = new UcAdmin( 'uploadcare', defined( 'UPLOADCARE_VERSION' ) ? UPLOADCARE_VERSION : '3.0.0' );
$project_info  = null;
$connect_error = null;
try {
	$project_info = $admin->projectInfo();
} catch ( \Exception $e ) {
	$connect_error = $e->getMessage();
}

/**
 * Save Uploadcare API keys
 *
 * @param string $public_key - public key.
 * @param string $secret_key - secret key.
 *
 * @return void
 */
function uc_save_api_keys( string $public_key, string $secret_key ): void {

	update_option( 'uploadcare_public', $public_key );
	update_option( 'uploadcare_secret', $secret_key );

	$new_keys_hash     = md5( $public_key . $secret_key );
	$current_timestamp = time();
	$saved_keys        = uc_get_saved_options();
	foreach ( $saved_keys as $option_data ) {
		try {
			$key_data = unserialize( $option_data['option_value'] );
			$key_hash = md5( $key_data['public_key'] . $key_data['secret_key'] );
			if ( $new_keys_hash === $key_hash ) {
				return;
			}
		} catch ( Throwable $tw ) {
			return;
		}
	}

	$data = array(
		'public_key' => $public_key,
		'secret_key' => $secret_key,
	);

	update_option( 'uploadcare_public_' . $current_timestamp, $data );
}

/**
 * Returns saved keys
 *
 * @return array
 */
function uc_get_saved_options(): array {
	global $wpdb;

	return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'options WHERE option_name LIKE %s', 'uploadcare_public_%' ), ARRAY_A );
}

?>

<?php if ( $saved ) : ?>
	<div class="updated"><p><strong><?php esc_html_e( 'Options saved.', 'uploadcare' ); ?></strong></p></div>
<?php endif; ?>
<?php if ( ! empty( $errors ) ) : ?>
	<div class="error">
		<?php foreach ( $code_errors as $code_error ) : ?>
			<p><strong><?php echo esc_html( $code_error ); ?></strong></p>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
<?php if ( $save_action && ! $keys_exists ) { ?>
	<div class="error">
		<p><strong><?php esc_html_e( 'API keys are not set' ); ?>.</strong></p>
	</div>
<?php } ?>

<?php if ( ! is_null( $connect_error ) ) { ?>
	<div class="error">
		<p>
			<strong><?php esc_html_e( 'Can\'t connect to the Uploadcare account. Check your public & secret keys. Follow plugin setup instructions down below.' ); ?></strong>
		</p>
		<p id="error-collapse-toggle" class="uc-toggle"
			style="color: #0d66c2; text-decoration: underline"><?php esc_html_e( 'More information' ); ?></p>
		<div data-toggle="error-collapse-toggle" class="uc-collapsed hide" style="margin-bottom: 1rem">
			<pre><small><?php echo esc_html( $connect_error ); ?></small></pre>
		</div>
	</div>
<?php } ?>

<?php if ( ! is_null( $project_info ) ) { ?>
	<div class="updated">
		<p>
			<?php $row_string = sprintf( 'Access to project <strong>"%s"</strong> successfully set up', esc_html( $project_info->getName() ) ); ?>
			<?php
			echo wp_kses(
				$row_string,
				$allowed_html
			);
			?>
		</p>
	</div>
<?php } ?>

<div class="wrap">
	<?php echo '<h2>' . esc_html_e( 'Uploadcare', 'uploadcare' ) . '</h2>'; ?>
	<?php
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$request_uri = str_replace( '%7E', '~', sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	} else {
		$request_uri = '';
	}
	?>
	<form name="oscimp_form" method="post" action="<?php esc_html( $request_uri ); ?>">
		<input type="hidden" name="uploadcare_hidden" value="Y">
		<?php wp_nonce_field( 'uc_save_action', 'uc_save_nonce' ); ?>

		<h3><?php esc_html_e( 'Plugin setup', 'uploadcare' ); ?></h3>
		<!-- <p><?php esc_html_e( 'VIDEO TUTORIAL', 'uploadcare' ); ?></p> -->

		<h4><?php esc_html_e( '1. Create an Uploadcare account', 'uploadcare' ); ?></h4>
		<p><?php esc_html_e( 'Sign up free', 'uploadcare' ); ?>
			<a href="https://uploadcare.com/pricing/" target="_blank"><?php esc_html_e( 'here', 'uploadcare' ); ?></a>.
		</p>
		<h4><?php esc_html_e( '2. Get your Uploadcare project API keys', 'uploadcare' ); ?> <a
				href="https://uploadcare.com/documentation/keys/" target="_blank">[?]</a></h4>
		<p><?php esc_html_e( 'Find API keys in your Uploadcare project\'s', 'uploadcare' ); ?>
			<a href="https://uploadcare.com/dashboard/"
				target="_blank"><?php esc_html_e( 'Dashboard', 'uploadcare' ); ?></a>
		</p>
		<p>
			<label for="uc_uploadcare_public"><?php esc_html_e( 'Public Key', 'uploadcare' ); ?>:</label>
			<input id="uc_uploadcare_public" type="text" name="uploadcare_public"
					value="<?php echo esc_html( $uploadcare_public ); ?>" size="50">
		</p>
		<p>
			<label for="uc_uploadcare_secret"><?php esc_html_e( 'Secret Key', 'uploadcare' ); ?>:</label>
			<input id="uc_uploadcare_secret" type="password" name="uploadcare_secret"
					value="<?php echo esc_html( $uploadcare_secret ); ?>" size="50">
		</p>

		<h4><?php esc_html_e( '3. Select Upload Sources', 'uploadcare' ); ?> <a
				href="https://uploadcare.com/docs/uploads/file_uploader/#upload-sources" target="_blank">[?]</a></h4>
		<?php
		foreach ( $uc_tabs as $tn => $single_tab ) {
			?>
			<p>
				<input name="uploadcare_source_tabs[]" id="st_<?php echo esc_html( $tn ); ?>" type="checkbox"
						value="<?php echo esc_html( $single_tab ); ?>" <?php echo \in_array( $single_tab, $uploadcare_source_tabs, true ) ? 'checked' : null; ?> />
                <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<label for="st_<?php echo esc_html( $tn ); ?>"><?php echo $single_tab, 'uploadcare'; ?></label>
			</p>
			<?php
		}
		?>

		<h4><?php echo esc_html__( '4. Start uploading', 'uploadcare' ); ?></h4>
		<?php
		$media_new = \get_site_url( null, '/wp-admin/media-new.php' );
		$edit_post = \get_site_url( null, '/wp-admin/edit.php' );
		$edit_page = \get_site_url( null, '/wp-admin/edit.php?post_type=page' );
		?>
		<p>
			<?php
			$string_data = sprintf(
			/* translators: 1: media_new, 2: edit_post, 3: edit_page */
				'Upload any file in <a href="%s" target="_blank">Media Library</a>, or choose <strong>Uploadcare Image</strong> when editing a <a href="%s" target="_blank">post</a> or a <a href="%s" target="_blank">page</a>.',
				esc_url( $media_new ),
				esc_url( $edit_post ),
				esc_url( $edit_page )
			);
			echo wp_kses(
				$string_data,
				$allowed_html
			);
			?>
		</p>

		<h3 id="uc-collapse-toggle"
			class="uc-show-hide uc-toggle"><?php esc_html_e( 'Advanced options', 'uploadcare' ); ?></h3>
		<div id="uc-advanced-options" data-toggle="uc-collapse-toggle" class="uc-collapsed hide">
			<h4><?php esc_html_e( 'Adaptive Delivery', 'uploadcare' ); ?> <a
					href="https://uploadcare.com/docs/delivery/adaptive_delivery/" target="_blank">[?]</a></h4>
			<p>
				<input name="uploadcare_adaptive_delivery" id="uc_uploadcare_adaptive_delivery" type="checkbox"
						value="1" <?php echo $uploadcare_adaptive_delivery ? 'checked' : null; ?>
				>
				<label for="uc_uploadcare_adaptive_delivery">
					<?php esc_html_e( 'Turn Adaptive Delivery ON.' ); ?>
				</label>
			</p>
			<p>
			<?php $text_var = 'Instead of regular responsive images, you can try our experimental technology. It adapts images to user context: screen size, browser, location, and other parameters. The optimization includes lazy loading, smart compression, WebP, responsive images, and retina display support. Add your domain to the list of allowed domains at <a href="https://uploadcare.com/dashboard/" target="_blank">Uploadcare Dashboard</a>, go to your project (e.g. "New project") — Delivery — Content delivery settings, and click “Integrate” Adaptive Delivery. Scroll to Step 2 and add your domain to the list of allowed domains. Click Done. That will enable Adaptive Delivery on your website.'; ?>
			<?php
			echo wp_kses(
				$text_var,
				$allowed_html
			);
			?>
			</p>
			<h4><?php esc_html_e( 'Backup', 'uploadcare' ); ?> <a
					href="https://uploadcare.com/docs/start/settings/#project-settings-advanced-backup" target="_blank">[?]</a>
			</h4>
			<p><?php esc_html_e( 'All your Uploadcare files are backed up automatically. Additionally, you can configure backups to your Amazong S3 Bucket in <a href="https://uploadcare.com/dashboard/" target="_blank">Dashboard</a>, Uploading settings.', 'uploadcare' ); ?></p>

			<h4><?php esc_html_e( 'Custom CDN CNAME', 'uploadcare' ); ?> <a
					href="https://uploadcare.com/community/t/how-to-set-up-custom-cdn-cname/40" target="_blank">[?]</a>
			</h4>
			<p>
				<label for="uc_uploadcare_cdn_base"><?php esc_html_e( 'Host', 'uploadcare' ); ?>:</label>
				<input id="uc_uploadcare_cdn_base" type="text" name="uploadcare_cdn_base"
						value="<?php echo esc_html( $uploadcare_cdn_base ); ?>" size="20">
			</p>

			<h4><?php esc_html_e( 'Secure Uploading', 'uploadcare' ); ?> <a
					href="https://uploadcare.com/docs/security/secure_uploads/" target="_blank">[?]</a></h4>
			<p><?php esc_html_e( 'Control who and when can upload files to your Uploadcare project.', 'uploadcare' ); ?></p>
			<p>
				<label for="uc_uploadcare_upload_lifetime">
					<?php esc_html_e( 'Set a lifetime in seconds (0 — disabled)', 'uploadcare' ); ?>:
				</label>
				<input id="uc_uploadcare_upload_lifetime" type="text" name="uploadcare_upload_lifetime"
						value="<?php echo esc_html( $uploadcare_upload_lifetime ); ?>" size="20">
			</p>
			<p><?php esc_html_e( 'Note: this feature will disable Adaptive Delivery for files that are not hosted with Uploadcare.', 'uploadcare' ); ?></p>
			<h4><?php esc_html_e( 'File Uploader fine tuning', 'uploadcare' ); ?> <a
					href="https://uploadcare.com/docs/uploads/file_uploader_options/" target="_blank">[?]</a></h4>
			<p>
				<label for="uc_uploadcare_finetuning">
					<?php echo esc_html( 'Insert a valid JSON object with correct parameters.' ); ?>
				</label>
			</p>
			<p>
				<textarea style="font-family: monospace" name="uploadcare_finetuning" id="uc_uploadcare_finetuning"
							rows="10"
							cols="75"><?php echo esc_html( \trim( \stripslashes( $uploadcare_finetuning ) ) ); ?></textarea>
			</p>
		</div>

		<?php submit_button(); ?>
	</form>
</div>

<script>
	(() => {
		document.querySelectorAll('.uc-toggle').forEach(toggle => {
			toggle.addEventListener('click', (e) => {
				const selector = '[data-toggle="' + e.target.id + '"]'
				document.querySelectorAll(selector).forEach(target => {
					target.classList.contains('hide') ? target.classList.remove('hide') : target.classList.add('hide');
				})
			})
		});
	})()
</script>
