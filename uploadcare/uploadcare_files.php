<?php
$public_key = get_option('uploadcare_public');
$secret_key = get_option('uploadcare_secret');
$api = new Uploadcare_Api($public_key, $secret_key);

$page = 1;
if (isset($_GET['page_num'])) {
	$page = $_GET['page_num'];
}

if (isset($_GET['delete'])) {
	$file_id = $_GET['file_id'];
	$file = $api->getFile($file_id);
	$file->delete();
}

$uri = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);

function change_param($uri, $param, $value) {
	$parsed = parse_url($uri);
	$path = $parsed['path'];
	$query = array();
	parse_str($parsed['query'], $query);
	$query[$param] = $value;
	return $path.'?'.http_build_query($query);
}
change_param($uri, '111', '111');

try {
	$files = $api->getFileList($page);
} catch (Exception $e) {
	$page = 1;
	$files = $api->getFileList($page);
}
$pagination_info = $api->getFilePaginationInfo();
?>
<div class="wrap">
	<div class="icon32">
		<img src="<?php echo plugins_url('uploadcare/logo_32.png'); ?>"
			width="32" />
	</div>
	<h2>Uploadcare Files</h2>

	<?php if ($pagination_info['pages'] > 1): ?>
	<div>
	Pages:
	<?php for ($i = 1; $i <= $pagination_info['pages']; $i++): ?>
		<?php if ($i == $page): ?>
			<span style="margin-left: 5px;"><?php echo $i; ?></span>
		<?php else: ?>
			<a href="<?php echo change_param($uri, 'page_num', $i);?>" style="margin-left: 5px;"><?php echo $i;?></a>
		<?php endif; ?>
	<?php endfor; ?>	
	<?php endif; ?>
	
		<div class="tablenav top">
			<div>
				<?php foreach ($files as $file): ?>
					<div style="float: left; width: 200px; height: 250px; margin-left: 10px; text-align: center;">
						<a href="<?php echo $file; ?>" target="_blank"><img src="<?php echo $file->scaleCrop(200, 200, true); ?>" /></a><br />
						<a href="<?php echo change_param(change_param($uri, 'delete', 'true'), 'file_id', $file->getFileId());?>" onclick="document.location.href=document.location.href+'&delete=true&file_id=<?php echo $file->getFileId(); ?>'" style="color: red;">Delete</a> | <a href="<?php echo $file; ?>" target="_blank">View</a>
					</div>
				<?php endforeach; ?>
			</div>
			<br class="clear">

	<?php if ($pagination_info['pages'] > 1): ?>
	<div>
	Pages:
	<?php for ($i = 1; $i <= $pagination_info['pages']; $i++): ?>
		<?php if ($i == $page): ?>
			<span style="margin-left: 5px;"><?php echo $i; ?></span>
		<?php else: ?>
			<a href="<?php echo change_param($uri, 'page_num', $i);?>" style="margin-left: 5px;"><?php echo $i;?></a>
		<?php endif; ?>
	<?php endfor; ?>	
	<?php endif; ?>			
			
</div>
