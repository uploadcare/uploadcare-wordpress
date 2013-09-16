<?php


function _uc_get_sizes() {
    global $_wp_additional_image_sizes;
    $sizes = array();
    foreach(get_intermediate_image_sizes() as $s) {
        $sizes[$s] = array(0, 0);
        if(in_array($s, array('thumbnail', 'medium', 'large'))) {
            $sizes[$s][0] = get_option($s . '_size_w');
            $sizes[$s][1] = get_option($s . '_size_h');
        } else {
            if (isset($_wp_additional_image_sizes) && isset($_wp_additional_image_sizes[$s])) {
                $sizes[$s] = array($_wp_additional_image_sizes[$s]['width'], $_wp_additional_image_sizes[$s]['height'],);
            }
        }
    }
    return $sizes;
}

/**
* Get thumbnail dimensions for size name
* @param string $size array|string Optional, default is 'thumbnail'. Size of image, either array or string.
* @return string Returns "{width}x{height}"
*/
function uc_thumbnail_size($size='thumbnail') {
    if(is_array($size)) {
        return implode('x', $size);
    }
    $sizes = _uc_get_sizes();

    return implode('x', $sizes[$size]);
}

?>
