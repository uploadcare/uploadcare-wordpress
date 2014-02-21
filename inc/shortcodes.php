<?php

add_shortcode('uploadcare', 'uploadcare_uploadcare_shortcode');
function uploadcare_uploadcare_shortcode() {
    wp_enqueue_script('uploadcare-shortcodes');

    $id = get_the_ID();
    $html = <<<HTML
Upload files:
<input class="uploadcare-uploader"
       data-multiple="true"
       data-post-id="$id"
       style="display:none;">
</input>
HTML;
    echo $html;
}

?>
