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
    'vk',
];
$tab_defaults = [
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

    if (!isset($_POST['uploadcare_source_tabs']) || empty($_POST['uploadcare_source_tabs'])) {
        $uploadcare_source_tabs[0] = 'file';
        $errors[] = __('You should select at least one source');
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
    $uploadcare_source_tabs       = get_option('uploadcare_source_tabs', $tab_defaults);
    $uploadcare_upload_lifetime   = get_option('uploadcare_upload_lifetime', '0');
    $uploadcare_adaptive_delivery = get_option('uploadcare_adaptive_delivery', true);
}

$admin = new UcAdmin('uploadcare', defined('UPLOADCARE_VERSION') ? UPLOADCARE_VERSION : '3.0.0');
$projectInfo = null;
$connectError = null;
try {
    $projectInfo = $admin->projectInfo();
} catch (\Exception $e) {
    $connectError = $e->getMessage();
}

$loader = new LocalMediaLoader();
$syncMessage = $loader->loadMedia();
if (isset($_POST['uc_sync_data']) && $_POST['uc_sync_data'] === 'sync') {
    echo \sprintf('<div class="updated"><p><strong>%s</strong></p><p>%s</p></div>', $syncMessage, __('Transfer process is started. Refresh your screen to see if it is finished.'));
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
        <p><strong><?= __('Cannot connect to Uploadcare account. Check your public / private keys')?></strong></p>
        <small><pre><?= $connectError?></pre></small>
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

        <h3><?= __('Set up Uploadcare plugin', 'uploadcare')?></h3>
        <!-- <p><?= __('VIDEO TUTORIAL', 'uploadcare')?></p> -->

        <h4><?= __('1. Get an Uploadcare account', 'uploadcare')?></h4>
        <p><?= \sprintf(__('Sign up <a href="https://uploadcare.com/pricing/" target="_blank">here</a>.', 'uploadcare')) ?></p>

        <h4><?= __('2. Set up your Uploadcare Project API keys', 'uploadcare') ?> <a href="https://uploadcare.com/documentation/keys/" target="_blank">[?]</a></h4>
        <p><?= \sprintf(__('Find API keys for your Uploadcare Project in <a href="https://uploadcare.com/dashboard/" target="_blank">Dashboard</a>:', 'uploadcare')) ?></p>
        <p>
            <label for="uc_uploadcare_public"><?= __('Public Key', 'uploadcare'); ?>:</label>
            <input id="uc_uploadcare_public" type="text" name="uploadcare_public"
                value="<?php echo $uploadcare_public; ?>" size="50">
        </p>
        <p>
            <label for="uc_uploadcare_secret"><?= __('Secret Key', 'uploadcare'); ?>:</label>
            <input id="uc_uploadcare_secret" type="text" name="uploadcare_secret"
                value="<?php echo $uploadcare_secret; ?>" size="50">
        </p>

        <h4><?= __('3. Transfer your existing Media Library to Uploadcare', 'uploadcare')?></h4>
        <p><?= __('This is required for <a href="https://uploadcare.com/products/adaptive-delivery/" target="_blank">Adaptive Delivery</a> to work. It moves all previously uploaded files from your <code>/wp-content/uploads/</code> folder to Uploadcare cloud and updates all articles with new image links.', 'uploadcare')?></p>
        <?php if (isset($_POST['uc_sync_data']) && $_POST['uc_sync_data'] === 'sync'): ?>
            <div style="display: inline-block; border: solid 1px #23a100; padding: 0 10px; line-height: 30px; border-radius: 4px;">
                <strong><?= __('Transfer in progress') ?></strong>
            </div>
        <?php else: ?>
            <?php if ($loader->getHasLocalMedia() === false): ?>
                <div
                    style="display: inline-block; border: solid 1px #23a100; padding: 0 10px; line-height: 30px; border-radius: 4px;">
                    <strong style="color: #23A100"><?= __('All your media files are successfully transfered to Uploadcare') ?></strong>
                </div>
            <?php else: ?>
                <button class="button" type="submit" value="sync" name="uc_sync_data">
                    <?php $loaderMediaCount = $loader->getLocalMediaCount() ?>
                    <?= \sprintf(
                        _n(
                            'Transfer %d Wordpress image to Uploadcare',
                            'Transfer %d Wordpress images to Uploadcare',
                            $loaderMediaCount,
                            'uploadcare'
                        ),
                        \number_format_i18n($loaderMediaCount)
                    ) ?>
                </button>
            <?php endif; ?>
        <?php endif; ?>

        <p><?= __("You're secure all way through. Only after the transfer process is completely finished, files will be removed from your WordPress hosting.", 'uploadcare')?></p>
        <p><?= __("If you accidentally upload new files with regular uploader, we'll suggest you to repeat the transfer to move new files to the cloud.", 'uploadcare')?></p>
        <p><?= __("In case you want to stop using Uploadcare plugin, this process is reversable: we'll download all files from Uploadcare cloud to your WordPress installation. See Advanced options for details.", 'uploadcare')?></p>

        <h4><?= __('4. Choose Upload Sources', 'uploadcare') ?> <a href="https://uploadcare.com/docs/uploads/file_uploader/#upload-sources" target="_blank">[?]</a></h4>
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

        <h3 id="uc-collapse-toggle" class="uc-show-hide"><?= __('Advanced options', 'uploadcare')?></h3>
        <div id="uc-advanced-options" class="uc-collapsed hide">
            <h4><?= __('Backup', 'uploadcare')?> <a href="https://uploadcare.com/docs/start/settings/#project-settings-advanced-backup" target="_blank">[?]</a></h4>
            <p><?= __('Uploadcare files are backed up automatically, but you can always configure your personal backup to a custom S3 Bucket (or Selected Storage). Connect the storage once, and the system will do backups on a timely basis. Set up backup in Uploadcare <a href="https://uploadcare.com/dashboard/" target="_blank">Dashboard</a> in Uploading section of your Project settings.', 'uploadcare')?></p>

            <h4><?= __('Custom CDN CNAME', 'uploadcare')?> <a href="https://uploadcare.com/community/t/how-to-set-up-custom-cdn-cname/40" target="_blank">[?]</a></h4>
            <p>
                <label for="uc_uploadcare_cdn_base"><?= __('Host', 'uploadcare'); ?>:</label>
                <input id="uc_uploadcare_cdn_base" type="text" name="uploadcare_cdn_base"
                       value="<?php echo $uploadcare_cdn_base; ?>" size="20">
            </p>

            <h4><?= __('Secure Uploads', 'uploadcare')?> <a href="https://uploadcare.com/docs/security/secure_uploads/" target="_blank">[?]</a></h4>
            <p>
                <label for="uc_uploadcare_upload_lifetime">
                    <?= __('Set lifetime in seconds (0 — disabled)', 'uploadcare'); ?>:
                </label>
                <input id="uc_uploadcare_upload_lifetime" type="text" name="uploadcare_upload_lifetime"
                       value="<?php echo $uploadcare_upload_lifetime; ?>" size="20">
            </p>

            <h4><?= __('Adaptive Delivery', 'uploadcare')?> <a href="https://uploadcare.com/docs/delivery/adaptive_delivery/" target="_blank">[?]</a></h4>
            <p>
                <input name="uploadcare_adaptive_delivery" id="uc_uploadcare_adaptive_delivery" type="checkbox"
                       value="1" <?= $uploadcare_adaptive_delivery ? 'checked' : null ?>
                >
                <label for="uc_uploadcare_adaptive_delivery">
                    <?= __("Turn off only if you explicitly want to disable it (if you plan doesn't allow it, it falls back to the regular delivery automatically).") ?>
                </label>
            </p>

            <h4><?= __('Widget fine tuning', 'uploadcare')?> <a href="https://uploadcare.com/docs/uploads/file_uploader_options/" target="_blank">[?]</a></h4>
            <p>
                <label for="uc_uploadcare_finetuning">
                    <?= __('Please remember that it must be a valid JSON object with upload widget parameters.'); ?>
                </label>
            </p>
            <p>
                <textarea style="font-family: monospace" name="uploadcare_finetuning" id="uc_uploadcare_finetuning" rows="10" cols="75"><?= \trim(\stripslashes($uploadcare_finetuning)) ?></textarea>
            </p>

            <h4><?= __('Move all your current images back to the local Media Library', 'uploadcare')?></h4>
            <p><?= __('This downloads all files from your Uploadcare project to the local Media Library (<code>/wp-content/uploads/</code>) and updates all articles with the local image links. Refresh this page later to check that all files were downloaded. This process is reversable, you can always upload them back, see the green button above.', 'uploadcare')?></p>
            <p>
            <button class="button" style="color: #990000; border-color: #aa0000" type="submit" value="sync" name="uc_download_data">
                <?= __('Download all your files back from Uploadcare') ?>
            </button>
        </p>
        </div>

        <?php submit_button(); ?>
    </form>
</div>

<script>
    (() => {
        document.getElementById('uc-collapse-toggle').addEventListener('click', () => {
            const target = document.getElementById('uc-advanced-options');
            if (target.classList.contains('hide'))
                target.classList.remove('hide')
            else
                target.classList.add('hide')
        });
    })()
</script>
