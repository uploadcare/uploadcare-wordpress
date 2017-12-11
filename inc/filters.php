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
        $url .= '-/stretch/off/-/scale_crop/' . $sz . '/center/';
    } else {
        $url = $uc_url;
    }
    return Array(
        $url,
        0, // width
        0, // height
        true,
    );
}

/**
 * Replace featured image HTML with Uploadcare image if:
 * - use uploadcare for featured images is set
 * - post's meta 'uploadcare_featured_image' is set
 * otherwise, uses default html code.
 * @legacy
 */
add_filter('post_thumbnail_html', 'uploadcare_post_thumbnail_html', 10, 5);
function uploadcare_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr) {
    if (!get_option('uploadcare_replace_featured_image')) {
        return $html;
    }

    $meta = get_post_meta($post_id, 'uploadcare_featured_image');
    if(empty($meta)) {
        return $html;
    }
    $url = $meta[0];
    $sz = uc_thumbnail_size($size);
    if($sz) {
        $src = "{$url}-/stretch/off/-/scale_crop/$sz/center/";
    } else {
        $src = $url;
    }
    $html = <<<HTML
<img src="{$src}"
     alt=""
/>
HTML;
    return $html;
}

/*
 * Add Uploadcare tab to defalt media upload tabs
 */
add_filter('media_upload_tabs', 'uploadcare_media_menu');
function uploadcare_media_menu($tabs) {
    $newtab = array(
        'uploadcare_files' => __('Uploadcare', 'uploadcare_files')
    );
    return array_merge($newtab, $tabs);
}


/*
 * Add Thumbnail column to Uploadcare User Images list in admin
 */
add_filter('manage_edit-uc_user_image_columns', 'uploadcare_add_uc_user_image_thumbnail_column', 5);
function uploadcare_add_uc_user_image_thumbnail_column($cols) {
    $cols['uploadcare_post_thumb'] = __('Thumb');
    return $cols;
}
