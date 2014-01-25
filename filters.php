<?php

add_filter('wp_get_attachment_url', 'uploadcare_get_attachment_url', 8, 2);
function uploadcare_get_attachment_url($url, $post_id) {

    if(! $uc_url = get_post_meta($post_id, 'uploadcare_url', true)) {
        return $url;
    }
    return $uc_url;
}

add_filter('image_downsize', 'uploadcare_image_downsize', 9, 3);
function uploadcare_image_downsize($value = false, $id, $size = 'medium') {
    if(! $uc_url = get_post_meta($id, 'uploadcare_url', true)) {
        return false;
    }

    $sz = uc_thumbnail_size($size);
    if($sz) {
        // chop filename part
        $url = preg_replace('/[^\/]*$/', '', $uc_url);
        $url .= '-/stretch/off/-/scale_crop/' . $sz . '/';
    } else {
        $url = $uc_url;
    }
    return Array(
        $url,
        0, // size
        0, // width
        0, // height
        false,
    );
}

?>
