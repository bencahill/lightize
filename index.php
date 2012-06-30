<?php

include "bootstrap.php";

$currentDir = !empty($_POST['dir'])?$_POST['dir']:NULL;

$dir = new LDirectory( $currentDir );
$images = $dir->getImages();

?>
<!DOCTYPE html>
<meta charset="utf-8">

<link rel="stylesheet" href="style.css">

<div id="main">
	<div id="dirs"></div>
	<div id="images">
		<div id="toolbar"></div>
		<div id="content">
			<ul>
<?php foreach( $images as $image ): ?>
				<li><img src="<?php echo "cache/$dir->name/$image->thumbnail" ?>"></li>
<?php endforeach; ?>
				<!-- To make justify work, up to ten items per row -->
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
			</ul>
		</div>
	</div>
</div>

<div id="status"></div>
