<?php
/*
Template Name: Oceans Template
*/
?>
<?php
$headerScripts = [
	'https://k8s.p4.greenpeace.org/international/wp-includes/js/jquery/jquery.js?ver=1.12.4',
	'https://k8s.p4.greenpeace.org/international/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1'
];
$footerScripts = [
	'https://k8s.p4.greenpeace.org/international/wp-content/themes/planet4-master-theme/main.js?ver=1542099183',
	'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js?ver=1.9.0',
	'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js?ver=1.14.3',
	'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/js/bootstrap.min.js?ver=4.1.1',
	'https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js?ver=2.0.8',
	'https://k8s.p4.greenpeace.org/international/wp-content/plugins/planet4-plugin-blocks/main.js?ver=1542099180',
	'https://k8s.p4.greenpeace.org/international/wp-includes/js/wp-embed.min.js?ver=4.9.8',
	'https://k8s.p4.greenpeace.org/international/wp-content/plugins/planet4-plugin-blocks/admin/js/submenu.js?ver=0.1',
];
$blocks = [
	'page-header',
	'covers-block__take-action',
	'covers-block__campaign-covers',
	'covers-block__content-covers',
	'articles-block',
	'carousel-block',
	'happy-point-block',
	'split-block',
	'column-block',
	'submenu-block',
	'take-action-task-block',
	'three-column-images-block',
	'two-column-block',
	'media-block'
];
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>Shortcake Theme Showcase - Greenpeace International</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<link rel="shortcut icon" type="image/ico" href="assets/child/favicon.ico"/>

	<link rel="stylesheet" id="bootstrap-css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css?ver=4.1.1" type='text/css' media='all'/>
	<link rel="stylesheet" id="slick-css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css?ver=1.9.0" type='text/css' media='all'/>
	<link rel="stylesheet" id="fork-awesome-css" href="https://cdnjs.cloudflare.com/ajax/libs/fork-awesome/1.1.1/css/fork-awesome.min.css?ver=1.1.1" type='text/css' media='all'/>
	<link rel="stylesheet" id="parent-style-css" href="<?php echo get_stylesheet_directory_uri() . '/parent.css'; ?>" type='text/css' media='all'/>
	<link rel="stylesheet" id="plugin-blocks-css" href="<?php echo get_stylesheet_directory_uri() . '/blocks.css'; ?>" type='text/css' media='all'/>
	<link rel="stylesheet" id="child-style-css" href="<?php echo get_stylesheet_directory_uri() . '/child.css'; ?>" type='text/css' media='all'/>

	<?php foreach ($headerScripts as $script) : ?>
		<script type='text/javascript' src='<?php echo $script; ?>'></script>
	<?php endforeach; ?>
</head>

<body class="page-template-default page page-id-18611 brown-bg theme-oceans">

<?php
if (in_array('page-header', $blocks)) {
	include_once get_stylesheet_directory() . '/page-elements/page-header.php';
}
?>

<div class="page-template">

	<a href="#" class="back-top">&nbsp;</a>

	<?php
	foreach ($blocks as $block) {
		if ($block !== 'page-header') {
			include_once get_stylesheet_directory() . '/page-elements/' . $block . '.php';
		}
	}
	?>
</div>

<?php foreach ($footerScripts as $script) : ?>
	<script type='text/javascript' src='<?php echo $script; ?>'></script>
<?php endforeach; ?>

</body>
</html>