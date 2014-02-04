<?php

add_shortcode('uploadcare', 'uploadcare_uploadcare_shortcode');
function uploadcare_uploadcare_shortcode() {
    $multiple = get_option('uploadcare_multiupload') ? "true" : "false";
    $params = array(
        'public_key' => get_option('uploadcare_public'),
        'multiple' => $multiple
    );

    wp_enqueue_script('uploadcare-shortcodes');
    wp_localize_script('uploadcare-shortcodes', 'WP_UC_PARAMS', $params);
    wp_enqueue_script('uploadcare-widget');

    echo '<input role="uploadcare-uploader" style="display:none;"></input>';
}

?>
