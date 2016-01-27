<?php


/*
 * Init Uploadcare plugin
 *
 * register scripts
 * register new Post type and Taxonomy
 */
add_action('init', 'uploadcare_plugin_init');
function uploadcare_plugin_init() {
    $api = uploadcare_api();
    $widget = new Uploadcare\Widget($api);
    $widget_url = $widget->getScriptSrc(UPLOADCARE_WIDGET_VERSION);
    wp_register_script('uploadcare-widget', $widget_url);

    wp_register_script(
        'uploadcare-config',
        UPLOADCARE_PLUGIN_URL . 'js/config.js',
        array('uploadcare-widget'));
    wp_localize_script('uploadcare-config', 'WP_UC_PARAMS', _uploadcare_get_js_cfg());

    wp_register_script(
        'uploadcare-main',
        UPLOADCARE_PLUGIN_URL . 'js/main.js',
        array('uploadcare-config'));

    wp_register_script(
        'uploadcare-shortcodes',
        UPLOADCARE_PLUGIN_URL . 'js/shortcodes.js',
        array('uploadcare-config'));
    wp_register_style(
        'uploadcare-style',
        UPLOADCARE_PLUGIN_URL . 'css/uploadcare.css'
    );

    _uploadcare_register_user_images();
}



/*
 * Add main.js script to certain pages
 */
add_action('admin_enqueue_scripts', 'add_uploadcare_js_to_admin');
function add_uploadcare_js_to_admin($hook) {
    if('post.php' != $hook &&
       'post-new.php' != $hook &&
       'media-new.php' != $hook &&
       'upload.php' != $hook) {
        // add js only on add and edit pages
        // and add media
        return;
    }

    wp_enqueue_script('uploadcare-main');
    wp_enqueue_style('uploadcare-style');
}


/*
 * Add Uploadcare button to toolbar
 */
add_action('media_buttons_context', 'uploadcare_add_media');
function uploadcare_add_media($context) {
    $api = uploadcare_api();

    $img = plugins_url('media/logo.png', dirname(__FILE__));
    $finetuning = stripcslashes(get_option('uploadcare_finetuning', ''));

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
 * @param $file Uploadcare\File object to attach
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

    if (get_option('uploadcare_download_to_server')) {
        $attached_file = uploadcare_download($file);
    } else {
        $attached_file = $file->data['original_file_url'];
        add_post_meta($attachment_id, 'uploadcare_url', $file->data['original_file_url'], true);
    }

    add_post_meta($attachment_id, '_wp_attached_file', $attached_file, true);
    add_post_meta($attachment_id, '_wp_attachment_metadata', $meta, true);
    return $attachment_id;
}

/**
 * Download image from Uploadcare and save it to local storage
 *
 * @param \Uploadcare\File $file
 * @return string
 */
function uploadcare_download(Uploadcare\File $file) {
    // downloading contents of image
    $contents = wp_remote_get($file);

    $dirInfo = wp_upload_dir();
    $absPath = $dirInfo['basedir'] . '/';
    $localFilename = 'uploadcare' . $dirInfo['subdir'] . '/' . basename($file) . '.jpg';

    // creating folders tree
    wp_mkdir_p($absPath . dirname($localFilename));

    // saving image
    file_put_contents($absPath . $localFilename, $contents['body']);

    return $localFilename;
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

    $attachment_id = uploadcare_attach($file);
    echo "{\"attach_id\": $attachment_id}";
    die;
}


/**
 * Create User Image post
 *
 * @param $file Uploadcare\File object to attach
 * @param $post_id int Post ID image should be attached to
 */
function uploadcare_attach_user_image($file, $post_id) {
    $attachment_id = uploadcare_attach($file);
    $currentuser = get_current_user_id();


    $filename = $file->data['original_filename'];
    $title = $filename;

    $user_image = array(
        'post_author'    => $currentuser,
        'post_date'      => date('Y-m-d H:i:s'),
        'post_type'      => 'uc_user_image',
        'post_title'     => $title,
        'post_parent'    => $post_id,
        'post_status'    => 'private',
        'post_mime_type' => $file->data['mime_type'],
    );

    $user_image_id = wp_insert_post($user_image, true);
    set_post_thumbnail($user_image_id, $attachment_id);
}

/**
 * Add ajax handler for uploadcare shortcode
 */
add_action('wp_ajax_uploadcare_shortcode_handle', 'uploadcare_shortcode_handle');
add_action('wp_ajax_nopriv_uploadcare_shortcode_handle', 'uploadcare_shortcode_handle');
function uploadcare_shortcode_handle() {
    // store file
    $api = uploadcare_api();
    $file_id = $_POST['file_id'];
    $post_id = $_POST['post_id'];
    $file = $api->getFile($file_id);

    // create user image
    uploadcare_attach_user_image($file, $post_id);
}


/**
 * Uploadcare tab in media library
 */
function uploadcare_media_files() {
    global $wpdb;
    require_once UPLOADCARE_PLUGIN_PATH . 'inc/uploadcare_media_files_menu_handle.php';
}


/**
 * Register Uploadcare tab in media library
 */
add_action('media_upload_uploadcare_files', 'uploadcare_media_files_menu_handle');
function uploadcare_media_files_menu_handle() {
    return wp_iframe('uploadcare_media_files');
}


add_action('post-upload-ui', 'uploadcare_media_upload');
function uploadcare_media_upload() {
    $img = plugins_url('media/logo.png', dirname(__FILE__));
    ?>

    <p class="uploadcare-picker">
      <a  id="uploadcare-post-upload-ui-btn"
          class="button button-hero"
          style="background: url('https://ucarecdn.com/assets/images/logo.png') no-repeat 5px 5px; padding-left: 44px;"
          href="javascript:ucPostUploadUiBtn();">
        Upload via Uploadcare
      </a>
    </p>
    <p class="max-upload-size">Maximum upload file size: 100MB (or more).</p>
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
    include('uploadcare_settings.php');
}


/*
 * Add Uploadcare settings page to admin
 */
add_action('admin_menu', 'uploadcare_settings_actions');
function uploadcare_settings_actions() {
    add_options_page('Uploadcare', 'Uploadcare', 'upload_files', 'uploadcare', 'uploadcare_settings');
}
