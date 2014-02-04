<?php


/*
 * Init Uploadcare plugin
 *
 * register scripts
 * register new Post type and Taxonomy
 */
add_action('init', 'uploadcare_plugin_init');
function uploadcare_plugin_init() {
    wp_register_script('uploadcare-widget', 'https://ucarecdn.com/widget/0.17.2/uploadcare/uploadcare-0.17.2.min.js');
    wp_register_script('uploadcare-main', UPLOADCARE_PLUGIN_URL . 'js/main.js');
    wp_register_script('uploadcare-shortcodes', UPLOADCARE_PLUGIN_URL . 'js/shortcodes.js');

    // _uploadcare_register_user_images();
}



/*
 * Add uploadcare-wp.js script to certain pages
 */
add_action('admin_enqueue_scripts', 'add_uploadcare_js_to_admin');
function add_uploadcare_js_to_admin($hook) {
    if('post.php' != $hook && 'post-new.php' != $hook) {
        // add js only on add and edit pages
        return;
    }

    $original = get_option('uploadcare_original') ? "true" : "false";
    $multiple = get_option('uploadcare_multiupload') ? "true" : "false";
    $params = array(
        'public_key' => get_option('uploadcare_public'),
        'original' => $original,
        'multiple' => $multiple
    );

    wp_enqueue_script('uploadcare-main');
    wp_localize_script('uploadcare-main', 'WP_UC_PARAMS', $params);
    wp_enqueue_script('uploadcare-widget');
}


/*
 * Add Uploadcare button to toolbar
 */
add_action('media_buttons_context', 'uploadcare_add_media');
function uploadcare_add_media($context) {
    $api = uploadcare_api();

    $img = plugins_url('logo.png', __FILE__);

    if(get_option('uploadcare_finetuning')) {
        $finetuning = stripcslashes(get_option('uploadcare_finetuning'));
    } else {
        $finetuning = '';
    }

    $context = <<<HTML
<div style="float: left">
  <a class="button" style="padding-left: .4em;" href="javascript: uploadcareMediaButton();">
    <span class="wp-media-buttons-icon" style="padding-right: 2px; vertical-align: text-bottom; background: url('{$img}') no-repeat 0px 0px;">
    </span>Add Media</a>
</div>
<div style="float: left">
  <a href="#" class="button insert-media add_media" data-editor="content" title="Wordpress Media Library">
    <span class="wp-media-buttons-icon"></span>Wordpress Media Library
  </a>
</div>
<style tyle="text/css">#wp-content-media-buttons>a:first-child { display: none }</style>
<script type="text/javascript">{$finetuning}</script>
HTML;
    return $context;
}


/**
 * Create WP attachment (add image to media library)
 *
 * @param $file Uploadcare File object to attach
 */
function uploadcare_attach($file) {
    $currentuser = get_current_user_id();
    $filename = $file->data['original_filename'];
    $title = $filename;

    $attachment = array(
        'post_author'    => $currentuser,
        'post_date'      => date('Y-m-d H:i:s'),
        'post_type'      => 'attachment',
        'post_title'     => $title,
        'post_parent'    => (!empty($_REQUEST['post_id']) ? $_REQUEST['post_id'] : null),
        'post_status'    => 'inherit',
        'post_mime_type' => $file->data['mime_type'],
    );

    $attachment_id = wp_insert_post($attachment, true);

    $meta = array('width' => $file->data['image_info']->width,
                  'height' => $file->data['image_info']->height);

    add_post_meta($attachment_id, '_wp_attached_file', $file->data['original_file_url'], true);
    add_post_meta($attachment_id, '_wp_attachment_metadata', $meta, true);
    add_post_meta($attachment_id, 'uploadcare_url', $file->data['original_file_url'], true);
}

/**
 * Add ajax upload handler
 */
add_action('wp_ajax_uploadcare_handle', 'uploadcare_handle');
function uploadcare_handle() {
    // store file
    $api = uploadcare_api();
    $file_id = $_POST['file_id'];
    $file = $api->getFile($file_id);
    $file->store();

    uploadcare_attach($file);
}


/**
 * Uploadcare tab in media library
 */
function uploadcare_media_files() {
    global $wpdb;
    require_once 'uploadcare_media_files_menu_handle.php';
}


/**
 * Register Uploadcare tab in media library
 */
add_action('media_upload_uploadcare_files', 'uploadcare_media_files_menu_handle');
function uploadcare_media_files_menu_handle() {
    return wp_iframe('uploadcare_media_files');
}


/**
 * Remove Featured Image Meta Box to be replaced with Uploadcare featured image box
 */
add_action('do_meta_boxes', 'uploadcare_remove_wp_featured_image_box');
function uploadcare_remove_wp_featured_image_box() {
    if (get_option('uploadcare_replace_featured_image')) {
        remove_meta_box('postimagediv', NULL, 'side');
    }
}


/**
 * Prints Uploadcare featured image box content.
 *
 * @param WP_Post $post The object for the current post/page.
 */
function uploadcare_featured_image_box($post) {
    // Add an nonce field so we can check for it later.
    wp_nonce_field('uploadcare_featured_image_box',
                 'uploadcare_featured_image_box_nonce');

    $value = get_post_meta($post->ID, 'uploadcare_featured_image', true);
    $html = <<<HTML
<a title="Set featured image"
   id="uc-set-featured-img"
   href="javascript:;"
   data-uc-url="{$value}">Set featured image</a>
<a title="Remove featured image"
   id="uc-remove-featured-img"
   href="javascript:;"
   class="hidden">Remove featured image</a>
<input type="hidden"
       id="uc-featured-image-input"
       name="uploadcare_featured_image"
       value="{$value}">
HTML;
    echo $html;
}


/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
add_action('add_meta_boxes', 'uploadcare_add_featured_image_box');
function uploadcare_add_featured_image_box($post_type) {
  if (get_option('uploadcare_replace_featured_image') &&
      post_type_supports($post_type, 'thumbnail')) {

      add_meta_box(
          'myplugin_sectionid',
          __('Featured Image (uploadcare)', 'uploadcare'),
          'uploadcare_featured_image_box',
          $post_type,
          'side'
      );
  }
}


/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
add_action('save_post', 'uploadcare_save_postdata');
function uploadcare_save_postdata($post_id) {
    // at the moment this is used only for featured images, so skip it if
    //   the option is not set
    if (!get_option('uploadcare_replace_featured_image')) {
        return $post_id;
    }

    /*
    * We need to verify this came from the our screen and with proper authorization,
    * because save_post can be triggered at other times.
    */

    // Check if our nonce is set.
    if (!isset( $_POST['uploadcare_featured_image_box_nonce'])) {
        return $post_id;
    }
    $nonce = $_POST['uploadcare_featured_image_box_nonce'];

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($nonce, 'uploadcare_featured_image_box')) {
        return $post_id;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check the user's permissions.
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    }

    /* OK, its safe for us to save the data now. */

    // Sanitize user input.
    $mydata = sanitize_text_field($_POST['uploadcare_featured_image']);

    // Update the meta field in the database.
    update_post_meta($post_id, 'uploadcare_featured_image', $mydata);
}


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


/*
 * Display Thumbnail column to Uploadcare User Images list in admin
 */
add_action('manage_uc_user_image_posts_custom_column', 'uploadcare_display_thumbnail_column', 5, 2);
function uploadcare_display_thumbnail_column($col, $id) {
    switch($col) {
        case 'uploadcare_post_thumb':
            if( function_exists('the_post_thumbnail') )
                echo the_post_thumbnail('thumbnail');
            else
                // echo 'Not supported in theme';
                echo '-';
            break;
    }
}


function uploadcare_settings() {
    include('inc/uploadcare_settings.php');
}


/*
 * Add Uploadcare settings page to admin
 */
add_action('admin_menu', 'uploadcare_settings_actions');
function uploadcare_settings_actions() {
    add_options_page('Uploadcare', 'Uploadcare', 'upload_files', 'uploadcare', 'uploadcare_settings');
}


?>
