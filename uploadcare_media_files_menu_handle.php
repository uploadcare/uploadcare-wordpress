<?php
    global $wp_version;
    list($wp_ver_main, $wp_ver_major, $wp_ver_minor) = explode('.', $wp_version);

    $public_key = get_option('uploadcare_public');
    $secret_key = get_option('uploadcare_secret');
    $api = new Uploadcare_Api($public_key, $secret_key);

    wp_enqueue_script('plupload-handlers');
    wp_enqueue_script('image-edit');
    wp_enqueue_script('set-post-thumbnail');
    wp_enqueue_style('imgareaselect');
    wp_enqueue_script('media-gallery');
    wp_enqueue_style('media');

    $type = 'uploadcare_files';

    $page = 1;
    if (isset($_GET['page_num'])) {
        $page = $_GET['page_num'];
    }

    function change_param($param, $value) {
        $uri = str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
        $parsed = parse_url($uri);
        $path = $parsed['path'];
        $query = array();
        parse_str($parsed['query'], $query);
        $query[$param] = $value;
        return $path.'?'.http_build_query($query);
    }

    function paginator($pages, $page) {
        if ($pages > 1) { ?>
    <div>
    Pages:
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php if ($i == $page): ?>
            <span style="margin-left: 5px;"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="<?php echo change_param('page_num', $i); ?>" style="margin-left: 5px;"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    </div>
    <?php
        }
    }

    $pagination_info = array();
    $count = $wpdb->get_row("SELECT COUNT(*) as count from {$wpdb->prefix}uploadcare");
    $pagination_info['pages'] = ceil($count->count / 20);
    $limit_floor = ($page - 1) * 20;
    $sql = <<<SQL
SELECT file_id, is_file, filename
FROM {$wpdb->prefix}uploadcare
ORDER BY `id` DESC LIMIT {$limit_floor},20
SQL;
    $files = $wpdb->get_results($sql);
?>
<script type="text/javascript">
  var win = window.dialogArguments || opener || parent || top;
</script>
<?php if ($wp_ver_main == 3 and $wp_ver_major < 5 ): ?>
<?php echo media_upload_header(); ?>
<?php endif; ?>
    <?php paginator($pagination_info['pages'], $page) ?>
    <div style="margin-top: 20px; margin-left: 10px;">
        <div>
            <?php foreach ($files as $_file): ?>
                <?php $file = $api->getFile($_file->file_id); ?>
                <div style="float: left; width: 110px; height: 110px; margin-left: 10px; margin-bottom: 10px; text-align: center;">
                    <a href="javascript: win.ucEditFile('<?php echo $_file->file_id?>');">
                        <?php if ($_file->is_file): ?>
                            <div style="width: 110px; height: 100px;line-height: 100px;">
                                <img src="https://ucarecdn.com/assets/images/logo.png" />
                            </div>
                            <br />
                            <?php echo $_file->filename;?>
                        <?php else: ?>
                            <img src="<?php echo $file->scaleCrop(100, 100, true); ?>" />
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <br class="clear">
    </div>
    <?php paginator($pagination_info['pages'], $page) ?>
