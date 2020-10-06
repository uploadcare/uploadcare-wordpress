<?php

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

$effects = array(
    'crop',
    'rotate',
    'mirror',
    'flip',
    'blur',
    'sharp',
    'enhance',
    'grayscale',
);

$effects_defaults = array(
    'crop',
    'rotate',
    'sharp',
    'enhance',
    'grayscale',
);


$saved = false;
if (isset($_POST['uploadcare_hidden']) && $_POST['uploadcare_hidden'] === 'Y') {
    $uploadcare_public = $_POST['uploadcare_public'];
    update_option('uploadcare_public', $uploadcare_public);
    $uploadcare_secret = $_POST['uploadcare_secret'];
    update_option('uploadcare_secret', $uploadcare_secret);
    $uploadcare_cdn_base = $_POST['uploadcare_cdn_base'];
    update_option('uploadcare_cdn_base', $uploadcare_cdn_base);
    $uploadcare_upload_lifetime = $_POST['uploadcare_upload_lifetime'];
    update_option('uploadcare_upload_lifetime', $uploadcare_upload_lifetime);
    $uploadcare_source_tabs = $_POST['uploadcare_source_tabs'];
    update_option('uploadcare_source_tabs', $uploadcare_source_tabs);
    $saved = true;
} else {
    $uploadcare_public = get_option('uploadcare_public');
    $uploadcare_secret = get_option('uploadcare_secret');
    $uploadcare_cdn_base = get_option('uploadcare_cdn_base', 'ucarecdn.com');
    $uploadcare_source_tabs = get_option('uploadcare_source_tabs', $tab_defaults);
    $uploadcare_upload_lifetime = get_option('uploadcare_upload_lifetime', '0');
}
?>

<?php if ($saved): ?>
    <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
<?php endif; ?>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <?php echo "<h2>" . __('Uploadcare', 'uploadcare_settings') . "</h2>"; ?>
    <form name="oscimp_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="uploadcare_hidden" value="Y">
        <h3>API Keys <a href="https://uploadcare.com/documentation/keys/">[?]</a></h3>
        <p>
            <label for="uc_uploadcare_public"><?php _e('Public key: '); ?></label>
            <input id="uc_uploadcare_public" type="text" name="uploadcare_public"
                   value="<?php echo $uploadcare_public; ?>" size="20">
        </p>
        <p>
            <label for="uc_uploadcare_secret"><?php _e("Secret key: "); ?></label>
            <input id="uc_uploadcare_secret" type="text" name="uploadcare_secret"
                   value="<?php echo $uploadcare_secret; ?>" size="20">
        </p>
        <h3>Options</h3>
        <p>
            <label for="uc_uploadcare_cdn_base"><?php _e("CDN Host: "); ?></label>
            <input id="uc_uploadcare_cdn_base" type="text" name="uploadcare_cdn_base"
                   value="<?php echo $uploadcare_cdn_base; ?>" size="20">
            <a href="https://uploadcare.com/community/t/how-to-set-up-custom-cdn-cname/40">[?]</a>
        </p>
        <p>
            <label for="uc_uploadcare_upload_lifetime"><?php _e("Signed uploads lifetime in seconds (0 - disabled): "); ?></label>
            <input id="uc_uploadcare_upload_lifetime" type="text" name="uploadcare_upload_lifetime"
                   value="<?php echo $uploadcare_upload_lifetime; ?>" size="20">
            <a href="https://uploadcare.com/docs/api_reference/upload/signed_uploads/">[?]</a>
        </p>

        <h3>Upload Sources</h3>
        <?php
        foreach ($tabs as $tn => $tab) {
            ?>
                <p>
                    <input name="uploadcare_source_tabs[]" id="st_<?= $tn?>" type="checkbox" value="<?= $tab?>" <?= \in_array($tab, $uploadcare_source_tabs, true) ? 'checked' : null ?> />
                    <label for="st_<?= $tn?>"><?= $tab?></label>
                </p>
            <?php
        }
        ?>

<!--        <select name="uploadcare_source_tabs[]" multiple="" size="12" style="width: 120px;">
            <?php
/*            $selected = in_array('all', $uploadcare_source_tabs) ? 'selected="selected"' : '';
            echo '<option ' . $selected . ' value="all">All tabs</option>';
            foreach ($tabs as $tab) {
                $selected = in_array($tab, $uploadcare_source_tabs) ? 'selected="selected"' : '';
                echo '<option ' . $selected . ' value="' . $tab . '">' . $tab . '</option>';
            }
            */?>
        </select>
-->        <p class="submit">
            <?php submit_button(); ?>
        </p>
    </form>
    <div>
        <ul>
            <li>Files uploaded to demo account (demopublickey) are deleted after some time.</li>
            <li>You can get your own account <a href="https://uploadcare.com/pricing/">here</a>.</li>
        </ul>
    </div>
</div>
