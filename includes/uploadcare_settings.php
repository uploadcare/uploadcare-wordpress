<?php

$tabs = [
    'file',
    'camera',
    'url',
    'facebook',
    'gdrive',
    'gphotos',
    'dropbox',
    'instagram',
    'evernote',
    'flickr',
    'onedrive',
    'box',
    'huddle',
    'vk',
];
$tab_defaults = [
    'file',
    'camera',
    'url',
    'dropbox',
    'facebook',
    'instagram',
    'gdrive',
    'gphotos',
];

$saved = false;
$errors = [];
if (isset($_POST['uploadcare_hidden']) && $_POST['uploadcare_hidden'] === 'Y') {
    $uploadcare_public = $_POST['uploadcare_public'];
    update_option('uploadcare_public', $uploadcare_public);
    $uploadcare_secret = $_POST['uploadcare_secret'];
    update_option('uploadcare_secret', $uploadcare_secret);
    $uploadcare_cdn_base = \str_replace('https://', '', $_POST['uploadcare_cdn_base']);
    update_option('uploadcare_cdn_base', $uploadcare_cdn_base);
    $uploadcare_upload_lifetime = $_POST['uploadcare_upload_lifetime'];
    update_option('uploadcare_upload_lifetime', $uploadcare_upload_lifetime);
    $uploadcare_finetuning = $_POST['uploadcare_finetuning'];
    update_option('uploadcare_finetuning', $uploadcare_finetuning);
//    $uploadcare_blink_loader = $_POST['uploadcare_blink_loader'];
//    update_option('uploadcare_blink_loader', $uploadcare_blink_loader);

    if (!isset($_POST['uploadcare_source_tabs']) || empty($_POST['uploadcare_source_tabs'])) {
        $uploadcare_source_tabs[0] = 'file';
        $errors[] = __('Select at least one source');
    } else {
        $uploadcare_source_tabs = $_POST['uploadcare_source_tabs'];
    }

    update_option('uploadcare_source_tabs', $uploadcare_source_tabs);
    $uploadcare_adaptive_delivery = isset($_POST['uploadcare_adaptive_delivery']) ? 1 : 0;
    update_option('uploadcare_adaptive_delivery', $uploadcare_adaptive_delivery);
    $saved = true;
} else {
    $uploadcare_public            = \trim(get_option('uploadcare_public'));
    $uploadcare_secret            = \trim(get_option('uploadcare_secret'));
    $uploadcare_cdn_base          = \trim(get_option('uploadcare_cdn_base', 'ucarecdn.com'));
    $uploadcare_finetuning        = \trim(get_option('uploadcare_finetuning'));
    $uploadcare_blink_loader      = \trim(get_option('uploadcare_blink_loader'));
    $uploadcare_source_tabs       = get_option('uploadcare_source_tabs', $tab_defaults);
    $uploadcare_upload_lifetime   = get_option('uploadcare_upload_lifetime', '0');
    $uploadcare_adaptive_delivery = get_option('uploadcare_adaptive_delivery', false);
}

$admin = new UcAdmin('uploadcare', defined('UPLOADCARE_VERSION') ? UPLOADCARE_VERSION : '3.0.0');
$projectInfo = null;
$connectError = null;
try {
    $projectInfo = $admin->projectInfo();
} catch (\Exception $e) {
    $connectError = $e->getMessage();
}
?>

<?php if ($saved): ?>
    <div class="updated"><p><strong><?= __('Options saved.', 'uploadcare'); ?></strong></p></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="error">
    <?php foreach ($errors as $error): ?>
    <p><strong><?= $error?></strong></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if($connectError !== null): ?>
    <div class="error">
        <p><strong><?= __('Can\'t connect to the Uploadcare account. Check your public & secret keys. Follow plugin setup instructions down below.')?></strong></p>
        <p id="error-collapse-toggle" class="uc-toggle" style="color: #0d66c2; text-decoration: underline"><?= __('More information') ?></p>
        <div data-toggle="error-collapse-toggle" class="uc-collapsed hide" style="margin-bottom: 1rem">
            <pre><small><?= $connectError?></small></pre>
        </div>
    </div>
<?php endif; ?>

<?php if ($projectInfo !== null): ?>
    <div class="updated">
        <p>
            <?= \sprintf(__('Access to project <strong>"%s"</strong> successfully set up'), $projectInfo->getName()) ?>
        </p>
    </div>
<?php endif; ?>

<div class="wrap">
    <?php echo "<h2>".__('Uploadcare', 'uploadcare')."</h2>"; ?>
    <form name="oscimp_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="uploadcare_hidden" value="Y">

        <h3><?= __('Plugin setup', 'uploadcare')?></h3>
        <!-- <p><?= __('VIDEO TUTORIAL', 'uploadcare')?></p> -->

        <h4><?= __('1. Create an Uploadcare account', 'uploadcare')?></h4>
        <p><?= \sprintf(__('Sign up free <a href="https://uploadcare.com/pricing/" target="_blank">here</a>.', 'uploadcare')) ?></p>

        <h4><?= __('2. Get your Uploadcare project API keys', 'uploadcare') ?> <a href="https://uploadcare.com/documentation/keys/" target="_blank">[?]</a></h4>
        <p><?= \sprintf(__('Find API keys in your Uploadcare project\'s <a href="https://uploadcare.com/dashboard/" target="_blank">Dashboard</a>:', 'uploadcare')) ?></p>
        <p>
            <label for="uc_uploadcare_public"><?= __('Public Key', 'uploadcare'); ?>:</label>
            <input id="uc_uploadcare_public" type="text" name="uploadcare_public"
                value="<?php echo $uploadcare_public; ?>" size="50">
        </p>
        <p>
            <label for="uc_uploadcare_secret"><?= __('Secret Key', 'uploadcare'); ?>:</label>
            <input id="uc_uploadcare_secret" type="password" name="uploadcare_secret"
                value="<?php echo $uploadcare_secret; ?>" size="50">
        </p>

        <h4><?= __('3. Select Upload Sources', 'uploadcare') ?> <a href="https://uploadcare.com/docs/uploads/file_uploader/#upload-sources" target="_blank">[?]</a></h4>
        <?php
        foreach ($tabs as $tn => $tab) {
            ?>
            <p>
                <input name="uploadcare_source_tabs[]" id="st_<?= $tn ?>" type="checkbox"
                       value="<?= $tab ?>" <?= \in_array($tab, $uploadcare_source_tabs, true) ? 'checked' : null ?> />
                <label for="st_<?= $tn ?>"><?= __($tab, 'uploadcare') ?></label>
            </p>
            <?php
        }
        ?>

        <h4><?= __('4. Start uploading', 'uploadcare') ?></h4>
        <?php
        $mediaNew = \get_site_url(null, '/wp-admin/media-new.php');
        $editPost = \get_site_url(null, '/wp-admin/edit.php');
        $editPage = \get_site_url(null, '/wp-admin/edit.php?post_type=page');
        ?>
        <p><?= \sprintf(__('Upload any file in <a href="%s" target="_blank">Media Library</a>, or choose <strong>Uploadcare Image</strong> when editing a <a href="%s" target="_blank">post</a> or a <a href="%s" target="_blank">page</a>.', 'uploadcare'),
            $mediaNew, $editPost, $editPage) ?></p>

        <h3 id="uc-collapse-toggle" class="uc-show-hide uc-toggle"><?= __('Advanced options', 'uploadcare')?></h3>
        <div id="uc-advanced-options" data-toggle="uc-collapse-toggle" class="uc-collapsed hide">
            <h4><?= __('Adaptive Delivery', 'uploadcare')?> <a href="https://uploadcare.com/docs/delivery/adaptive_delivery/" target="_blank">[?]</a></h4>
            <p>
                <input name="uploadcare_adaptive_delivery" id="uc_uploadcare_adaptive_delivery" type="checkbox"
                    value="1" <?= $uploadcare_adaptive_delivery ? 'checked' : null ?>
                >
                <label for="uc_uploadcare_adaptive_delivery">
                    <?= __("Turn Adaptive Delivery ON.") ?>
                </label>
            </p>
            <p><?= \sprintf(__('Instead of regular responsive images, you can try our experimental technology. It adapts images to user context: screen size, browser, location, and other parameters. The optimization includes lazy loading, smart compression, WebP, responsive images, and retina display support. Add your domain to the list of allowed domains at <a href="https://uploadcare.com/dashboard/" target="_blank">Uploadcare Dashboard</a>, go to your project (e.g. "New project") — Delivery — Content delivery settings, and click “Integrate” Adaptive Delivery. Scroll to Step 2 and add your domain to the list of allowed domains. Click Done. That will enable Adaptive Delivery on your website.', 'uploadcare')) ?></p>

            <h4><?= __('Backup', 'uploadcare')?> <a href="https://uploadcare.com/docs/start/settings/#project-settings-advanced-backup" target="_blank">[?]</a></h4>
            <p><?= __('All your Uploadcare files are backed up automatically. Additionally, you can configure backups to your Amazong S3 Bucket in <a href="https://uploadcare.com/dashboard/" target="_blank">Dashboard</a>, Uploading settings.', 'uploadcare')?></p>

            <h4><?= __('Custom CDN CNAME', 'uploadcare')?> <a href="https://uploadcare.com/community/t/how-to-set-up-custom-cdn-cname/40" target="_blank">[?]</a></h4>
            <p>
                <label for="uc_uploadcare_cdn_base"><?= __('Host', 'uploadcare'); ?>:</label>
                <input id="uc_uploadcare_cdn_base" type="text" name="uploadcare_cdn_base"
                       value="<?php echo $uploadcare_cdn_base; ?>" size="20">
            </p>

            <h4><?= __('Secure Uploading', 'uploadcare')?> <a href="https://uploadcare.com/docs/security/secure_uploads/" target="_blank">[?]</a></h4>
            <p><?= __('Control who and when can upload files to your Uploadcare project.', 'uploadcare')?></p>
            <p>
                <label for="uc_uploadcare_upload_lifetime">
                    <?= __('Set a lifetime in seconds (0 — disabled)', 'uploadcare'); ?>:
                </label>
                <input id="uc_uploadcare_upload_lifetime" type="text" name="uploadcare_upload_lifetime"
                       value="<?php echo $uploadcare_upload_lifetime; ?>" size="20">
            </p>
            <p><?= __('Note: this feature will disable Adaptive Delivery for files that are not hosted with Uploadcare.', 'uploadcare')?></p>

            <!-- <h4><?= __('Adaptive Delivery options', 'uploadcare')?> <a href="https://uploadcare.com/docs/delivery/adaptive_delivery/#adaptive-integrate-sdk">[?]</a></h4>
            <p>
                <label for="uc_uploadcare_blink_loader">
                    <?= __('Insert a valid JSON object with correct parameters (everything is true by default).'); ?>
                </label>
            </p>
            <p>
                <textarea style="font-family: monospace" name="uploadcare_blink_loader" id="uc_uploadcare_blink_loader" rows="8" cols="75" placeholder='{&#10;  "fadeIn": true,&#10;  "lazyload": true,&#10;  "smartCompression": true,&#10;  "responsive": true,&#10;  "retina": true,&#10;  "webp": true&#10;}'><?= \trim(\stripslashes($uploadcare_blink_loader)) ?></textarea>
            </p> -->

            <h4><?= __('File Uploader fine tuning', 'uploadcare')?> <a href="https://uploadcare.com/docs/uploads/file_uploader_options/" target="_blank">[?]</a></h4>
            <p>
                <label for="uc_uploadcare_finetuning">
                    <?= __('Insert a valid JSON object with correct parameters.'); ?>
                </label>
            </p>
            <p>
                <textarea style="font-family: monospace" name="uploadcare_finetuning" id="uc_uploadcare_finetuning" rows="10" cols="75"><?= \trim(\stripslashes($uploadcare_finetuning)) ?></textarea>
            </p>
        </div>

        <?php submit_button(); ?>
    </form>
</div>

<script>
    (() => {
        document.querySelectorAll('.uc-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                const selector = '[data-toggle="' + e.target.id + '"]'
                document.querySelectorAll(selector).forEach(target => {
                    target.classList.contains('hide') ? target.classList.remove('hide') : target.classList.add('hide');
                })
            })
        });
    })()
</script>
