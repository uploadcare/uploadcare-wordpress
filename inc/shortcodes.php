<?php

add_shortcode('uploadcare', 'uploadcare_uploadcare_shortcode');
function uploadcare_uploadcare_shortcode() {
    wp_enqueue_script('uploadcare-shortcodes');
    $finetuning = stripcslashes(get_option('uploadcare_finetuning', ''));
    $id = get_the_ID();

    $html = <<<HTML
<script type="text/javascript">{$finetuning}</script>
Upload files:
<input class="uploadcare-uploader"
       data-multiple="true"
       data-post-id="$id"
       style="display:none;">
</input>
HTML;
    echo $html;
}
