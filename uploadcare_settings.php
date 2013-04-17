<?php 
$saved = false;
if($_POST['uploadcare_hidden'] == 'Y') {
	$uploadcare_public = $_POST['uploadcare_public'];
	update_option('uploadcare_public', $uploadcare_public);
	$uploadcare_secret = $_POST['uploadcare_secret'];
	update_option('uploadcare_secret', $uploadcare_secret);
	$saved = true;
} else {
	$uploadcare_public = get_option('uploadcare_public');
	$uploadcare_secret = get_option('uploadcare_secret');
}
?>
<?php if ($saved): ?>
<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>  
<?php endif; ?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
    <?php    echo "<h2>" . __( 'Uploadcare Settings', 'uploadcare_settings' ) . "</h2>"; ?>
    <form name="oscimp_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
        <input type="hidden" name="uploadcare_hidden" value="Y">  
        <p><?php _e("Public key: " ); ?><input type="text" name="uploadcare_public" value="<?php echo $uploadcare_public; ?>" size="20"><?php _e(" ex: demopublickey" ); ?></p>  
        <p><?php _e("Secret key: " ); ?><input type="text" name="uploadcare_secret" value="<?php echo $uploadcare_secret; ?>" size="20"><?php _e(" ex: demoprivatekey" ); ?></p>  
        <p class="submit">  
        <?php submit_button(); ?>  
        </p>  
    </form>  
    <div>
    <ul>
    	<li>File at demo account (demopublickey) are deleted sometimes.</li>
    	<li>You can get your own account here: <a href="https://uploadcare.com/accounts/create/">https://uploadcare.com/accounts/create/</a></li>
    </ul>
    </div>
</div>