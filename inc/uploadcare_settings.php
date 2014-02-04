<?php
$saved = false;
if($_POST['uploadcare_hidden'] == 'Y') {
    $uploadcare_public = $_POST['uploadcare_public'];
    update_option('uploadcare_public', $uploadcare_public);
    $uploadcare_secret = $_POST['uploadcare_secret'];
    update_option('uploadcare_secret', $uploadcare_secret);
    $uploadcare_original = $_POST['uploadcare_original'];
    update_option('uploadcare_original', $uploadcare_original);
    $uploadcare_multiupload = $_POST['uploadcare_multiupload'];
    update_option('uploadcare_multiupload', $uploadcare_multiupload);
    $uploadcare_finetuning = $_POST['uploadcare_finetuning'];
    update_option('uploadcare_finetuning', $uploadcare_finetuning);
    $uploadcare_featured = $_POST['uploadcare_featured'];
    update_option('uploadcare_replace_featured_image', $uploadcare_featured);
    $saved = true;
} else {
    $uploadcare_public = get_option('uploadcare_public');
    $uploadcare_secret = get_option('uploadcare_secret');
    $uploadcare_multiupload = get_option('uploadcare_multiupload');
    $uploadcare_finetuning = get_option('uploadcare_finetuning');
    $uploadcare_featured = get_option('uploadcare_replace_featured_image');
}
?>

<?php if ($saved): ?>
<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
<?php endif; ?>

<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
    <?php echo "<h2>" . __( 'Uploadcare', 'uploadcare_settings' ) . "</h2>"; ?>
    <form name="oscimp_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="uploadcare_hidden" value="Y">
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
        <p>
            <input type="checkbox" name="uploadcare_original" <?php if ($uploadcare_original): ?>checked="checked"<?php endif; ?>
            />&nbsp;<?php _e('Insert image with URL to the original image'); ?>
        </p>
        <p>
            <input type="checkbox" name="uploadcare_featured" <?php if ($uploadcare_featured): ?>checked="checked"<?php endif; ?>
            />&nbsp;<?php _e('Use Uploadcare for featured images'); ?>
        </p>
        <p>
            <input type="checkbox" name="uploadcare_multiupload" <?php if ($uploadcare_multiupload): ?>checked="checked"<?php endif; ?>
            />&nbsp;<?php _e('Allow multiupload in Uploadcare widget'); ?>
        </p>
        <p>
            <?php _e('Uploadcare widget fine tuning'); ?>
            (<a href="https://uploadcare.com/documentation/widget/#advanced-configuration"><?php _e('see documentation'); ?></a>)<br>
            <textarea name="uploadcare_finetuning" rows="10" cols="50"><?php echo stripcslashes($uploadcare_finetuning); ?></textarea>
        </p>
        <p class="submit">
        <?php submit_button(); ?>
        </p>
    </form>
    <div>
    <ul>
        <li>File at demo account (demopublickey) are deleted after some time.</li>
        <li>You can get your own account here: <a href="https://uploadcare.com/accounts/create/">https://uploadcare.com/accounts/create/</a></li>
    </ul>
    </div>
</div>
