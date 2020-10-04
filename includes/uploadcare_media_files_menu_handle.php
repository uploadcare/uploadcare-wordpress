<?php
    global $wp_version;

    define('UC_PER_LOAD_LIMIT', 24);

    list($wp_ver_main, $wp_ver_major) = explode('.', $wp_version);

    $api = uploadcare_api();

    wp_enqueue_script('uploadcare-main');
    wp_enqueue_style('media');
    wp_enqueue_style('uploadcare-style');

    $type = 'uploadcare_files';

    $files = $api->getFileList(array(
        'request_limit' => UC_PER_LOAD_LIMIT,
    ));

    $pages = ceil(count($files) / UC_PER_LOAD_LIMIT);
    $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
    if ($page < 1 || $page > $pages) {
        $page = 1;
    }
    $start = ($page - 1) * UC_PER_LOAD_LIMIT;
    $end = min($start + UC_PER_LOAD_LIMIT, count($files));

    function change_param($param, $value) {
        $uri = str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
        $parsed = parse_url($uri);
        $path = $parsed['path'];
        $query = array();
        parse_str($parsed['query'], $query);
        $query[$param] = $value;
        return $path . '?' . http_build_query($query);
    }

    $paginator = function() use($pages, $page, $start, $end, $files) {
        if ($pages > 1) {
            ?>
            <div class="paginator">
            <?php
            if ($page > 1) {
                ?>
                <a href="<?php echo change_param('page_num', $page - 1); ?>" class="browser button button-hero" data-navi="prev">&laquo; Previous</a>
                <?php
            }
            if ($page < $pages) {
                ?>
                <a href="<?php echo change_param('page_num', $page + 1); ?>" class="browser button button-hero" data-navi="next">Next &raquo;</a>
                <?php
            }
            ?>
                <span>Showing <?php echo $start + 1; ?> - <?php echo $end; ?> of <?php echo count($files); ?> pics</span>
            </div>
            <?php
        }
    };

    if ($wp_ver_main == 3 and $wp_ver_major < 5 ) {
        echo media_upload_header();
    }

    echo $api->widget->getScriptTag();
    ?>

    <script type="text/javascript">
      var win = window.dialogArguments || opener || parent || top;
    </script>

    <div id="uploadcare-lib-container">
    <div style="padding-top: 20px; margin-left: 10px;">
        <div>
            <?php for ($i = $start; $i < $end; $i++):
                /** @var Uploadcare\File $file */
                $file = $files[$i];
            ?>
                <div style="float: left; width: 110px; height: 110px; margin-left: 10px; margin-bottom: 10px; text-align: center;">
                    <a href="javascript: ucEditFile('<?php echo $file->getFileId() ?>');">
                        <?php if ($file->is_file): ?>
                            <div style="width: 110px; height: 100px;line-height: 100px;">
                                <img src="https://ucarecdn.com/assets/images/logo.png" />
                            </div>
                            <br />
                            <?php echo $file->filename ?>
                        <?php else: ?>
                            <img src="<?php echo $file->scaleCrop(100, 100, true); ?>" />
                        <?php endif; ?>
                    </a>
                </div>
            <?php endfor; ?>
        </div>
        <br class="clear">
    </div>
    </div>
    <div id="uploadcare-panel-container"></div>
    <div id="uploadcare-more-container">
        <?php $paginator(); ?>
        <a href="javascript:;" class="browser button button-hero" id="uploadcare-more">Upload more</a>
    </div>
