<?php

add_shortcode('uploadcare', 'uploadcare_uploadcare_shortcode');
function uploadcare_uploadcare_shortcode() {
    wp_enqueue_script('uploadcare-shortcodes');

    echo '<input role="uploadcare-uploader" style="display:none;"></input>';
}

?>
