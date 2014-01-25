<?php

// add_action('post-upload-ui', 'uploadcare_media_upload');
function uploadcare_media_upload() {

    // if ( $post = get_post() )
    //     $browser_uploader .= '&amp;post_id=' . intval( $post->ID );
    // elseif ( ! empty( $GLOBALS['post_ID'] ) )
    //     $browser_uploader .= '&amp;post_id=' . intval( $GLOBALS['post_ID'] );

    $img = plugins_url('logo.png', __FILE__);

    ?>

    <p class="uploadcare-picker">
      <a class="button" style="padding-left: .4em;" href="javascript: uploadcareMediaButton();">
        <span class="wp-media-buttons-icon" style="padding-right: 2px; vertical-align: text-bottom; background: url('<?php $img ?>') no-repeat 0px 0px;">
      </span>Upload via Uploadcare
        </a>
    </p>
    <?php
}

?>
