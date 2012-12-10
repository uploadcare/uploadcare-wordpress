<?php
	$public_key = get_option('uploadcare_public');
	$secret_key = get_option('uploadcare_secret');
	$api = new Uploadcare_Api($public_key, $secret_key);
	
	wp_enqueue_script('plupload-handlers');
	wp_enqueue_script('image-edit');
	wp_enqueue_script('set-post-thumbnail');
	wp_enqueue_style('imgareaselect');
	wp_enqueue_script('media-gallery');
	wp_enqueue_style('media');
	
	$type = 'uploadcare';
	$post_id = intval($_REQUEST['post_id']);
	
	$form_action_url = admin_url("media-upload.php?type=$type&tab=uploadcare&post_id=$post_id");
	$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type);
	$form_class = 'media-upload-form type-form validate';
	
	//POST
	$file = null;
	$scale_crop_default_width = 800;
	$scale_crop_default_height = 634;
	if ($_POST['save']) {
		$file_id = $_POST['file_id'];
		$file = $api->getFile($file_id);
		$file->scaleCrop($scale_crop_default_width, $scale_crop_default_height);
		$file->op('stretch/off');
		$file->store();
	}
	$is_insert = false;
	$is_preview = false;
	if ($_POST['insert'] or $_POST['_preview']) {
		$file_id = $_POST['file_id'];
		$file = $api->getFile($file_id);
		$original = clone $file;
		
		if (isset($_POST['crop'])) {
			$crop_width = $_POST['crop_width'];
			$crop_height = $_POST['crop_height'];
			$crop_center = isset($_POST['crop_center']) ? true : false;
			$crop_fill_color = $_POST['crop_fill_color'];
			$file = $file->crop($crop_width, $crop_height, $crop_center, $crop_fill_color);
		}
		
		if (isset($_POST['resize'])) {
			$resize_width = $_POST['resize_width'];
			$resize_height = $_POST['resize_height'];
			$file = $file->resize($resize_width, $resize_height);
		}		
		
		if (isset($_POST['scale_crop'])) {
			$scale_crop_width = $_POST['scale_crop_width'];
			$scale_crop_height = $_POST['scale_crop_height'];
			$scale_crop_center = isset($_POST['scale_crop_center']) ? true : false;
			$file = $file->scaleCrop($scale_crop_width, $scale_crop_height, $scale_crop_center);
		} else {
			$scale_crop_width = $scale_crop_default_width;
			$scale_crop_height = $scale_crop_default_height;
			$scale_crop_center = false;
		}
		
		if (isset($_POST['effect_flip'])) {
			$file = $file->effect('flip');
		}
		
		if (isset($_POST['effect_grayscale'])) {
			$file = $file->effect('grayscale');
		}		
		
		if (isset($_POST['effect_invert'])) {
			$file = $file->effect('invert');
		}		
		
		if (isset($_POST['effect_mirror'])) {
			$file = $file->effect('mirror');
		}		
		
		$file->op('stretch/off');
		
		$is_insert = true;
		
		if ($_POST['_preview']) {
			$is_insert = false;
			$is_preview = true;
		}		
	}
	
?>
<?php if ($is_preview): ?>
<?php echo $file->getImgTag($file->data['original_filename']); ?>
<?php die();?>
<?php endif;?>
<?php if ($is_insert): ?>
<script type="text/javascript">
/* <![CDATA[ */
var win = window.dialogArguments || opener || parent || top;
win.send_to_editor('<a href=\"<?php echo $original->getUrl($file->data['original_filename']); ?>\"><img src=\"<?php echo $file->getUrl($file->data['original_filename']); ?>\" alt=\"\" /></a>');
/* ]]> */
</script>
<?php die();?>
<?php endif;?>

<?php echo media_upload_header(); ?>
<?php if ($file): ?>
<div id="media-items">
<div class="media-item">
<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr($form_action_url); ?>" class="<?php echo $form_class; ?>" id="<?php echo $type; ?>-form">
	<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
	<input type="hidden" name="file_id" id="file_id" value="<?php echo $file_id; ?>" />

	
	<table class="slidetoggle describe startclosed" style="display: table;">
		<thead class="media-item-info">
		<tr>
			<td colspan="2">
				<p><strong>File name:</strong> <?php echo $file->data['original_filename']; ?></p>
				<p><strong>File type:</strong> <?php echo $file->data['mime_type']; ?></p>
				<p><strong>Upload date:</strong> <?php echo $file->data['upload_date']; ?></p>
			</td>
		</tr>
		</thead>
		<tbody>
		
			<tr>
				<td colspan="2"><input type="checkbox" name="crop"/>&nbsp;<strong>Crop</strong></td>
			</tr>
			<tr><th class="label"><label>Width:</label></th><td><input type="text" name="crop_width"/></td></tr>
			<tr><th class="label"><label>Height:</label></th><td><input type="text" name="crop_height"/></td></tr>
			<tr><th class="label"><label>Center:</label></th><td><input type="checkbox" name="crop_center"/></td></tr>
			<tr><th class="label"><label>Fill color:</label></th><td><input type="text" name="crop_fill_color"/></td></tr>

			<tr>
				<td colspan="2"><input type="checkbox" name="resize"/>&nbsp;<strong>Resize</strong></td>
			</tr>
			<tr><th class="label"><label>Width:</label></th><td><input type="text" name="resize_width"/></td></tr>
			<tr><th class="label"><label>Height:</label></th><td><input type="text" name="resize_height"/></td></tr>	
			
			<tr>
				<td colspan="2"><input type="checkbox" name="scale_crop" checked="checked" />&nbsp;<strong>Scale crop</strong></td>
			</tr>
			<tr><th class="label"><label>Width:</label></th><td><input type="text" name="scale_crop_width" value="<?php echo $scale_crop_default_width;?>" /></td></tr>
			<tr><th class="label"><label>Height:</label></th><td><input type="text" name="scale_crop_height" value="<?php echo $scale_crop_default_height; ?>" /></td></tr>
			<tr><th class="label"><label>Center:</label></th><td><input type="checkbox" name="scale_crop_center" checked="checked"/></td></tr>
			
			<tr>
				<td colspan="2"><strong>Effects</strong></td>
			</tr>
			<tr><th class="label" colspan="2"><input type="checkbox" name="effect_flip" />&nbsp;<label>Flip</label></th></tr>
			<tr><th class="label" colspan="2"><input type="checkbox" name="effect_grayscale" />&nbsp;<label>Grayscale</label></th></tr>
			<tr><th class="label" colspan="2"><input type="checkbox" name="effect_invert" />&nbsp;<label>Invert</label></th></tr>
			<tr><th class="label" colspan="2"><input type="checkbox" name="effect_mirror" />&nbsp;<label>Mirror</label></th></tr>
			
			<tr valign="top">
				<td class="A1B1" colspan="2">
					<p><strong>Preview:</strong></p>
					<div id="uploadcare_preview" style="width: 600px; overflow-x: scroll;">
						<?php echo $file->getImgTag($file->data['original_filename']); ?>
					</div>
				</td>
			</tr>			
			
		</tbody>
		</table>	
	<?php submit_button( __( 'Insert into post' ), 'button', 'insert', false ); ?>
</form>
</div>
</div>
<script type="text/javascript">
jQuery(function() {
	jQuery('#<?php echo $type; ?>-form :input').change(function() {
		var form = jQuery('#<?php echo $type; ?>-form');
		var data = form.serialize();
		data += '&_preview=true';
		jQuery.post(
				form.attr('action'),
				data,
				function (html) {
					jQuery('#uploadcare_preview').html(html);
				}
		);
		return false;
	});
});
</script>
<?php else: ?>
<?php echo $api->widget->getScriptTag(); ?>
<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr($form_action_url); ?>" class="<?php echo $form_class; ?>" id="<?php echo $type; ?>-form">
<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
<?php wp_nonce_field('media-form'); ?>
	<h3 class="media-title">Use Uploadcare widget to upload file.</h3>
	<?php echo $api->widget->getInputTag('file_id'); ?>
	<p class="savebutton ml-submit">
	<?php submit_button( __( 'Store File' ), 'button', 'save', false ); ?>
	</p>	
</form>

<script type="text/javascript">
jQuery(function() {
	jQuery('#<?php echo $type; ?>-form').submit(function() {
		var form = jQuery(this);
		var file_id = form.find('input[name=file_id]').val();
		if (!file_id) {
			return false; 
		}
	});
});
</script>
<?php endif; ?>