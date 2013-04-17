<?php
function uploadcare_settings() {
	include('uploadcare_settings.php');
}

function uploadcare_setings_actions() {
	add_options_page('Uploadcare Setting', 'Uploadcare Settings', 'upload_files', 'uploadcare', 'uploadcare_settings');
}
add_action('admin_menu', 'uploadcare_setings_actions');