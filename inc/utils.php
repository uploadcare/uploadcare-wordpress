<?php

use \Uploadcare;

/**
 * Get Api object
 *
 */
function uploadcare_api() {
    global $wp_version;
    $user_agent = 'Uploadcare Wordpress ' . UPLOADCARE_PLUGIN_VERSION . '/' . $wp_version;
    return new Uploadcare\Api(
        get_option('uploadcare_public'),
        get_option('uploadcare_secret'),
        $user_agent
    );
}


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

function _uc_get_size_array($size) {
    if(is_array($size)) {
        return $size;
    }
    $sizes = _uc_get_sizes();
    if(array_key_exists($size, $sizes)) {
        $arr = $sizes[$size];

        // handle "unlimited" width
        // 9999 -> 2048
        // WP uses 9999 to indicate unlimited width for images,
        // at the moment max width for ucarecdn operaions is 2048
        if($arr[1] == 9999) {
            $arr[1] = 2048;
        }
        return $arr;
    }
    return false;
}

/**
* Get thumbnail dimensions for size name
* @param string $size array|string Optional, default is 'thumbnail'. Size of image, either array or string.
* @return string Returns "{width}x{height}"
*/
function uc_thumbnail_size($size = 'thumbnail') {
    $arr = _uc_get_size_array($size);
    if(!$arr) {
        return false;
    }
    return implode('x', $arr);
}

function uc_thumbnail_width($size = 'thumbnail') {
    $arr = _uc_get_size_array($size);
    if(!$arr) {
        return false;
    }
    return $arr[0];
}

function uc_thumbnail_height($size = 'thumbnail') {
    $arr = _uc_get_size_array($size);
    if(!$arr) {
        return false;
    }
    return $arr[1];
}


function _uploadcare_register_user_images() {
    $image_type_labels = array(
        'name' => _x('User images', 'post type general name'),
        'singular_name' => _x('Uploadcare User Image', 'post type singular name'),
        'add_new' => _x('Add New User Image', 'image'),
        'add_new_item' => __('Add New User Image'),
        'edit_item' => __('Edit User Image'),
        'new_item' => __('Add New User Image'),
        'all_items' => __('View User Images'),
        'view_item' => __('View User Image'),
        'search_items' => __('Search User Images'),
        'not_found' =>  __('No User Images found'),
        'not_found_in_trash' => __('No User Images found in Trash'),
        'parent_item_colon' => '',
        'menu_name' => 'User Images'
    );

    $image_type_args = array(
        'labels' => $image_type_labels,
        'public' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'map_meta_cap' => true,
        'menu_position' => null,
        'menu_icon' => 'dashicons-art',
        'supports' => array('title', 'author', 'thumbnail')
    );

    $type = register_post_type('uc_user_image', $image_type_args);

    $image_category_labels = array(
        'name' => _x( 'User Image Categories', 'taxonomy general name' ),
        'singular_name' => _x( 'User Image', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search User Image Categories' ),
        'all_items' => __( 'All User Image Categories' ),
        'parent_item' => __( 'Parent User Image Category' ),
        'parent_item_colon' => __( 'Parent User Image Category:' ),
        'edit_item' => __( 'Edit User Image Category' ),
        'update_item' => __( 'Update User Image Category' ),
        'add_new_item' => __( 'Add New User Image Category' ),
        'new_item_name' => __( 'New User Image Name' ),
        'menu_name' => __( 'User Image Categories' ),
      );

    $image_category_args = array(
        'hierarchical' => true,
        'labels' => $image_category_labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'uploadcare_user_image_category'),
    );

    register_taxonomy('uploadcare_user_image_category', array('uc_user_image'), $image_category_args);
}


/*
 * Build config array for javascript
 */
function _uploadcare_get_js_cfg() {
    $tab_options = (array)get_option('uploadcare_source_tabs', array(
        'file',
        'url',
        'facebook',
        'instagram',
        'flickr',
        'gdrive',
        'evernote',
        'box',
        'skydrive',
    ));

    if(in_array('all', $tab_options)) {
        $tabs = 'all';
    } else {
        $tabs = implode(' ', $tab_options);
    }
    return array(
        'public_key' => get_option('uploadcare_public'),
        'original' => get_option('uploadcare_original') ? "true" : "false",
        'multiple' => get_option('uploadcare_multiupload') ? "true" : "false",
        'ajaxurl' => admin_url('admin-ajax.php'),
        'tabs' => $tabs,
    );
}

/**
 * Print base settings (used both on)
 */
function print_base_settings($submitUrl = null) {

    $submitUrl = $submitUrl ?: str_replace('%7E', '~', $_SERVER['REQUEST_URI']);

    $tabs = array(
        'file',
        'url',
        'facebook',
        'instagram',
        'flickr',
        'gdrive',
        'evernote',
        'box',
        'skydrive',
        'dropbox',
        'vk'
    );
    $tab_defaults = array(
        'file',
        'url',
        'facebook',
        'instagram',
        'flickr',
        'gdrive',
        'evernote',
        'box',
        'skydrive',
    );

    $saved = false;
    if(isset($_POST['uploadcare_hidden']) && $_POST['uploadcare_hidden'] == 'Y') {
        $uploadcare_public = $_POST['uploadcare_public'];
        update_option('uploadcare_public', $uploadcare_public);
        $uploadcare_secret = $_POST['uploadcare_secret'];
        update_option('uploadcare_secret', $uploadcare_secret);
        $uploadcare_original = $_POST['uploadcare_original'];
        update_option('uploadcare_original', $uploadcare_original);
        $uploadcare_multiupload = $_POST['uploadcare_multiupload'];
        update_option('uploadcare_multiupload', $uploadcare_multiupload);
        $uploadcare_download_to_server = $_POST['uploadcare_download_to_server'];
        update_option('uploadcare_download_to_server', $uploadcare_download_to_server);
        $uploadcare_finetuning = $_POST['uploadcare_finetuning'];
        update_option('uploadcare_finetuning', $uploadcare_finetuning);
        $uploadcare_source_tabs = $_POST['uploadcare_source_tabs'];
        update_option('uploadcare_source_tabs', $uploadcare_source_tabs);
        $saved = true;
    } else {
        $uploadcare_public = get_option('uploadcare_public');
        $uploadcare_secret = get_option('uploadcare_secret');
        $uploadcare_original = get_option('uploadcare_original');
        $uploadcare_multiupload = get_option('uploadcare_multiupload');
        $uploadcare_download_to_server = get_option('uploadcare_download_to_server');
        $uploadcare_finetuning = get_option('uploadcare_finetuning');
        $uploadcare_source_tabs = get_option('uploadcare_source_tabs', $tab_defaults);
    }

    if ($saved) {
?>
        <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
<?php
    }
?>
    <form name="oscimp_form" method="post" action="<?php echo $submitUrl; ?>">
        <input type="hidden" name="uploadcare_hidden" value="Y">
        <h3>API Keys <a href="https://uploadcare.com/documentation/keys/">[?]</a></h3>
        <p>
            <?php _e('Public key: '); ?>
            <input type="text" name="uploadcare_public" value="<?php echo $uploadcare_public; ?>" size="20">
            <?php _e('ex: demopublickey'); ?>
        </p>
        <p>
            <?php _e("Secret key: " ); ?>
            <input type="text" name="uploadcare_secret" value="<?php echo $uploadcare_secret; ?>" size="20">
            <?php _e('ex: demoprivatekey'); ?>
        </p>
        <h3>Options</h3>
        <p>
            <input type="checkbox" name="uploadcare_original" <?php if ($uploadcare_original): ?>checked="checked"<?php endif; ?>
                />&nbsp;<?php _e('Insert image with URL to the original image'); ?>
        </p>
        <p>
            <input type="checkbox" name="uploadcare_multiupload" <?php if ($uploadcare_multiupload): ?>checked="checked"<?php endif; ?>
                />&nbsp;<?php _e('Allow multiupload in Uploadcare widget'); ?>
        </p>
        <p>
            <input type="checkbox" name="uploadcare_download_to_server" <?php if ($uploadcare_download_to_server): ?>checked="checked"<?php endif; ?>
                />&nbsp;<?php _e('Download images to server from Uploadcare before publish'); ?>
        </p>
        <h3>Source tabs</h3>
        <select name="uploadcare_source_tabs[]" multiple="" size="12" style="width: 120px;">
            <?php
            $selected = in_array('all', $uploadcare_source_tabs) ? 'selected="selected"' : '';
            echo '<option ' . $selected . ' value="all">All tabs</option>';
            foreach ($tabs as $tab) {
                $selected = in_array($tab, $uploadcare_source_tabs) ? 'selected="selected"' : '';
                echo '<option ' . $selected . ' value="' . $tab . '">' . $tab . '</option>';
            }
            ?>
        </select>

        <h3>Widget fine tuning <a href="https://uploadcare.com/documentation/widget/#advanced-configuration">[?]</a></h3>
        <p>
            <textarea name="uploadcare_finetuning" rows="10" cols="50"><?php echo stripcslashes($uploadcare_finetuning); ?></textarea>
        </p>
        <p class="submit">
            <?php submit_button(); ?>
        </p>
    </form>
    <div>
        <ul>
            <li>Files uploaded to demo account (demopublickey) are deleted after some time.</li>
            <li>You can get your own account <a href="https://uploadcare.com/pricing/">here</a>.</li>
        </ul>
    </div>
<?php
}
